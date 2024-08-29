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
    $post_id = $_POST['post_id']; // 9
    $user_id = $_POST['user_id']; // 23

    // 防止 SQL 注入
    $post_id = mysqli_real_escape_string($connectNow, $post_id);
    $user_id = mysqli_real_escape_string($connectNow, $user_id);

    // 首先从 posts 表中获取 post_user_id
    $postQuery = "SELECT user_id FROM posts WHERE id = ?";
    $postStmt = $connectNow->prepare($postQuery);

    if ($postStmt) {
        $postStmt->bind_param('s', $post_id);
        $postStmt->execute();
        $postResult = $postStmt->get_result();

        if ($postResult->num_rows > 0) {
            $postRow = $postResult->fetch_assoc();
            $post_user_id = $postRow['user_id']; // 39

            $sql = "SELECT comments.id, comments.user_id, comments.post_id, comments.post_user_id, comments.emoji_id, comments.content, users.photo AS avatar_url
                    FROM comments 
                    JOIN users ON comments.user_id = users.id 
                    WHERE comments.post_id = ? AND comments.post_user_id = ? AND comments.user_id = ?";

            $stmt = $connectNow->prepare($sql);

            if ($stmt) {
                $stmt->bind_param('sss', $post_id, $post_user_id, $post_user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                $comments = array();

                while ($row = $result->fetch_assoc()) {
                    $comments[] = array(
                        "id" => $row['id'],
                        "user_id" => $row['user_id'],
                        "post_id" => $row['post_id'],
                        "post_user_id" => $row['post_user_id'],
                        "emoji_id" => $row['emoji_id'],
                        "content" => $row['content'],
                        "avatar_url" => $row['avatar_url'], // users table's photo
                    );
                }

                if (!empty($comments)) {
                    echo json_encode(array("success" => true, "comments" => $comments));
                } else {
                    echo json_encode(array("success" => false, "message" => "No comments found for the given post and user."));
                }

                $stmt->close();
            } else {
                echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
            }

        } else {
            echo json_encode(array("success" => false, "message" => "Post not found or no user associated with this post."));
        }

        $postStmt->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }

} else {
    echo json_encode(array("success" => false, "message" => "Missing post_id or user_id parameter."));
}

$connectNow->close();
