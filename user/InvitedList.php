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
    $friend_id = $_POST['friend_id']; // 23

    // 處理 SQL 注入攻擊
    $friend_id = mysqli_real_escape_string($connectNow, $friend_id);

    // 查詢 friends 表中的 user_id 或 friend_id，分開記錄
    $sql = "SELECT 
                CASE 
                    WHEN user_id = ? THEN friend_id
                    WHEN friend_id = ? THEN user_id
                END AS related_id,
                user_id, friend_id
            FROM friends
            WHERE (friend_id = ? OR user_id = ?) AND status = 0";
    
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        // 綁定變數到 SQL 語句
        $statement->bind_param('ssss', $friend_id, $friend_id, $friend_id, $friend_id);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $friends_sent_by_me = array();  // 我發送的好友請求
            $friends_received_by_me = array();  // 別人發送給我的好友請求

            while ($row = $result->fetch_assoc()) {
                $related_id = $row['related_id'];

                // 判斷是我發送的請求還是我收到的請求
                if ($row['user_id'] == $friend_id) {
                    // 當前用戶是 user_id，我發送的請求
                    $userSql = "SELECT `name`, `photo` FROM `users` WHERE `id`=?";
                    $userStatement = $connectNow->prepare($userSql);

                    if ($userStatement) {
                        $userStatement->bind_param('i', $related_id);
                        $userStatement->execute();
                        $userStatement->bind_result($name, $photo);
                        $userStatement->fetch();

                        if ($name && $photo) {
                            $friends_sent_by_me[] = array(
                                "id" => (string)$related_id,
                                "name" => $name,
                                "photo" => 'http://163.22.32.24/smiley_backend/img/photo/' . $photo
                            );
                        }
                        $userStatement->close();
                    }
                } elseif ($row['friend_id'] == $friend_id) {
                    // 當前用戶是 friend_id，我收到的請求
                    $userSql = "SELECT `name`, `photo` FROM `users` WHERE `id`=?";
                    $userStatement = $connectNow->prepare($userSql);

                    if ($userStatement) {
                        $userStatement->bind_param('i', $related_id);
                        $userStatement->execute();
                        $userStatement->bind_result($name, $photo);
                        $userStatement->fetch();

                        if ($name && $photo) {
                            $friends_received_by_me[] = array(
                                "id" => (string)$related_id,
                                "name" => $name,
                                "photo" => 'http://163.22.32.24/smiley_backend/img/photo/' . $photo
                            );
                        }
                        $userStatement->close();
                    }
                }
            }

            // 返回兩個列表，分別表示我發出的好友請求和收到的好友請求
            echo json_encode(array(
                "success" => true,
                "sent_by_me" => $friends_sent_by_me,
                "sent_by_other" => $friends_received_by_me
            ));
        } else {
            echo json_encode(array("success" => false, "message" => "No related users found."));
        }
        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing id parameter."));
}

$connectNow->close();
