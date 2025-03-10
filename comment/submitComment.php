<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include '../connection.php';

if (!$connectNow) {
    echo json_encode(array("success" => false, "message" => "Database connection failed"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array("success" => false, "message" => "Invalid request method."));
    exit();
}

if (isset($_POST['user_id']) && isset($_POST['post_id']) && (isset($_POST['emoji_id']) || isset($_POST['content']))) {
    $user_id = $_POST['user_id'];
    $post_id = $_POST['post_id'];
    $emoji_id = isset($_POST['emoji_id']) ? $_POST['emoji_id'] : null;
    $content = isset($_POST['content']) ? $_POST['content'] : null;

    // 防止 SQL 注入
    $user_id = mysqli_real_escape_string($connectNow, $user_id);
    $post_id = mysqli_real_escape_string($connectNow, $post_id);
    $emoji_id = mysqli_real_escape_string($connectNow, $emoji_id);
    $content = mysqli_real_escape_string($connectNow, $content);

    // 1. 查找 posts 表中符合 post_id 的 user_id，並將其作為 post_user_id 插入 comments 表中
    $sql_post_user = "SELECT user_id FROM posts WHERE id=?";
    $statement_post_user = $connectNow->prepare($sql_post_user);

    if ($statement_post_user) {
        $statement_post_user->bind_param('s', $post_id);
        $statement_post_user->execute();
        $result_post_user = $statement_post_user->get_result();

        if ($row_post_user = $result_post_user->fetch_assoc()) {
            $post_user_id = $row_post_user['user_id'];

            // 2. 插入數據到 comments 表中
            $sql_insert_comment = "INSERT INTO comments (user_id, post_id, emoji_id, content, post_user_id) VALUES (?, ?, ?, ?, ?)";
            $statement_insert_comment = $connectNow->prepare($sql_insert_comment);

            if ($statement_insert_comment) {
                // 綁定參數並執行插入操作
                $statement_insert_comment->bind_param('sssss', $user_id, $post_id, $emoji_id, $content, $post_user_id);
                if ($statement_insert_comment->execute()) {
                    // 獲取插入的 id
                    $inserted_id = $connectNow->insert_id;

                    // 更新 pos 欄位
                    $sql_update_pos = "UPDATE comments SET pos=? WHERE id=?";
                    $statement_update_pos = $connectNow->prepare($sql_update_pos);
                    if ($statement_update_pos) {
                        $statement_update_pos->bind_param('ii', $inserted_id, $inserted_id);
                        $statement_update_pos->execute();
                        $statement_update_pos->close();
                    }

                    echo json_encode(array("success" => true, "message" => "submitPost success"));
                } else {
                    echo json_encode(array("success" => false, "message" => "Execute failed: " . $statement_insert_comment->error));
                }
                $statement_insert_comment->close();
            } else {
                echo json_encode(array("success" => false, "message" => "Prepare insert comment failed: " . $connectNow->error));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Post not found for the given post_id."));
        }

        $statement_post_user->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare post_user query failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing parameter."));
}

$connectNow->close();

