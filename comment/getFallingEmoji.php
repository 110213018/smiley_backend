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

if (isset($_POST['post_id']) && isset($_POST['user_id'])) {
    $id = $_POST['post_id'];
    $user_id = $_POST['user_id'];

    // 防止 SQL 注入
    $id = mysqli_real_escape_string($connectNow, $id);
    $user_id = mysqli_real_escape_string($connectNow, $user_id);

    // 使用 JOIN 將 comments 表與 users 表結合，獲取 photo
    $sql = "SELECT comments.id, comments.user_id, comments.post_id, comments.post_user_id, comments.emoji_id, comments.content, users.photo AS avatar_url
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE comments.post_id = ? 
            AND (
                (comments.post_user_id != ? AND comments.user_id = ?) 
                OR 
                (comments.post_user_id = ? AND comments.user_id != ?)
            )AND(
                comments.emoji_id != '0')";

    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('sssss', $id, $user_id, $user_id, $user_id, $user_id);
        $statement->execute();
        $result = $statement->get_result();

        $contents = array();
        
        while ($row = $result->fetch_assoc()) {
            $contents[] = array(
                "id" => $row['id'],
                "user_id" => $row['user_id'],
                "post_id" => $row['post_id'],
                "post_user_id" => $row['post_user_id'],
                "emoji_id" => $row['emoji_id'],
                "content" => $row['content'],
                "avatar_url" => $row['avatar_url'],
            );
        }
        
        if (!empty($contents)) {
            echo json_encode(array("success" => true, "comments" => $contents));
        } else {
            echo json_encode(array("success" => false, "message" => "No comments found for the given post and user."));
        }

        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing post_id or user_id parameter."));
}

$connectNow->close();