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

    // 查詢 friends 表，根據 user_id 和 friend_id 的匹配情況選擇對應的 ID
    $friends_sql = "SELECT 
                        CASE 
                            WHEN `friend_id` = ? THEN `user_id` 
                            WHEN `user_id` = ? THEN `friend_id` 
                        END AS `friend_user_id`
                    FROM `friends` 
                    WHERE (`user_id` = ? OR `friend_id` = ?) 
                    AND `status` = 1";
                    
    $friends_statement = $connectNow->prepare($friends_sql);

    if ($friends_statement) {
        $friends_statement->bind_param('ssss', $user_id, $user_id, $user_id, $user_id);
        $friends_statement->execute();
        $friends_result = $friends_statement->get_result();

        $friend_ids = array();
        while ($friend_row = $friends_result->fetch_assoc()) {
            $friend_ids[] = $friend_row['friend_user_id'];
        }
        $friends_statement->close();

        if (!empty($friend_ids)) {
            // 準備查詢 posts 表，僅限於找到的 friend_user_id 列表
            $placeholders = implode(',', array_fill(0, count($friend_ids), '?'));
            $sql = "SELECT `text_color`, `background_color`, `monster`, `angel`, `title`, `date`, `content` 
                    FROM `posts` 
                    WHERE `user_id` IN ($placeholders)
                    AND `date` BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 DAY) AND CURDATE() 
                    ORDER BY `date` DESC";

            $statement = $connectNow->prepare($sql);

            if ($statement) {
                $types = str_repeat('s', count($friend_ids));
                $statement->bind_param($types, ...$friend_ids);
                $statement->execute();
                $result = $statement->get_result();

                $posts = array();
                while ($row = $result->fetch_assoc()) {
                    $posts[] = array(
                        "text_color" => $row['text_color'],
                        "background_color" => $row['background_color'],
                        "monster" => $row['monster'],
                        "angel" => $row['angel'],
                        "title" => $row['title'],
                        "date" => $row['date'],
                        "content" => $row['content'],
                    );
                }

                if (!empty($posts)) {
                    echo json_encode(array("success" => true, "posts" => $posts));
                } else {
                    echo json_encode(array("success" => false, "message" => "No posts found for the given user and date."));
                }

                $statement->close();
            } else {
                echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "No friends found with status = 1 for the given user."));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing user_id parameter."));
}

$connectNow->close();
