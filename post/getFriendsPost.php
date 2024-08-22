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

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // 防止 SQL 注入
    $user_id = mysqli_real_escape_string($connectNow, $user_id);

    // 查找用戶的好友
    $friends_sql = "
        SELECT 
            CASE 
                WHEN friend_id = ? THEN user_id 
                WHEN user_id = ? THEN friend_id 
            END AS friend_user_id
        FROM friends 
        WHERE (user_id = ? OR friend_id = ?) 
        AND status = 1";
    
    $statement = $connectNow->prepare($friends_sql);

    if ($statement) {
        $statement->bind_param('ssss', $user_id, $user_id, $user_id, $user_id);
        $statement->execute();
        $result = $statement->get_result();

        $friend_user_ids = array();
        
        while ($row = $result->fetch_assoc()) {
            $friend_user_ids[] = $row['friend_user_id'];
        }

        $statement->close();

        if (empty($friend_user_ids)) {
            echo json_encode(array("success" => false, "message" => "No friends found."));
            $connectNow->close();
            exit();
        }

        // 查找這些好友的貼文
        $friend_user_ids_placeholder = implode(',', array_fill(0, count($friend_user_ids), '?'));
        $posts_sql = "
            SELECT posts.id, posts.user_id, posts.text_color, posts.background_color, posts.monster, posts.angel, posts.title, posts.date, posts.content, 
                   users.photo AS user_photo, users.name AS user_name
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE posts.user_id IN ($friend_user_ids_placeholder) 
            AND posts.date BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 DAY) AND CURDATE() 
            ORDER BY posts.date DESC";

        $statement = $connectNow->prepare($posts_sql);

        if ($statement) {
            $types = str_repeat('i', count($friend_user_ids));
            $statement->bind_param($types, ...$friend_user_ids);
            $statement->execute();
            $result = $statement->get_result();

            $posts = array();
            
            while ($row = $result->fetch_assoc()) {
                $posts[] = array(
                    "id" => $row['id'],
                    "user_id" => $row['user_id'],
                    "text_color" => $row['text_color'],
                    "background_color" => $row['background_color'],
                    "monster" => $row['monster'],
                    "angel" => $row['angel'],
                    "title" => $row['title'],
                    "date" => $row['date'],
                    "content" => $row['content'],
                    "user_photo" => $row['user_photo'],
                    "user_name" => $row['user_name']
                );
            }
            
            if (!empty($posts)) {
                echo json_encode(array("success" => true, "posts" => $posts));
            } else {
                echo json_encode(array("success" => false, "message" => "No posts found for the given friends and date."));
            }

            $statement->close();
        } else {
            echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }

    $connectNow->close();
} else {
    echo json_encode(array("success" => false, "message" => "Missing user_id parameter."));
}
