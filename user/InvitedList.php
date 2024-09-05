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

if (isset($_POST['friend_id'])) {
    $friend_id = $_POST['friend_id']; //23

    // 處理 SQL 注入攻擊
    $friend_id = mysqli_real_escape_string($connectNow, $friend_id);

    // 查詢 friends 表中的 user_id 或 friend_id
    $sql = "SELECT 
                CASE 
                    WHEN user_id = ? THEN friend_id
                    WHEN friend_id = ? THEN user_id
                END AS related_id
            FROM friends
            WHERE (friend_id = ? OR user_id = ?) AND status = 0";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        // 將變數綁定到 SQL 語句
        $statement->bind_param('ssss', $friend_id, $friend_id, $friend_id, $friend_id);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $users = array();
            while ($row = $result->fetch_assoc()) {
                $user_id = $row['related_id'];

                // 使用 `user_id` 查詢 `users` 表中的 `name` 和 `photo`
                $userSql = "SELECT `name`, `photo` FROM `users` WHERE `id`=?";
                $userStatement = $connectNow->prepare($userSql);

                if ($userStatement) {
                    $userStatement->bind_param('i', $user_id);
                    $userStatement->execute();
                    $userStatement->bind_result($friend_id, $photo);
                    $userStatement->fetch();

                    // ip 要改 192.168.56.1 -> 163.22.32.24
                    if ($friend_id && $photo) {
                        $users[] = array(
                            "id" => (string)$user_id,
                            "name" => $friend_id,
                            "photo" => 'http://163.22.32.24/smiley_backend/img/photo/' . $photo
                        );
                    }
                    $userStatement->close();
                } else {
                    echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
                    exit();
                }
            }
            echo json_encode(array(
                "success" => true,
                "users" => $users
            ));
        } else {
            echo json_encode(array("success" => false, "message" => "User not found."));
        }
        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing id parameter."));
}

$connectNow->close();