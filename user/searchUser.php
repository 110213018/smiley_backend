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
    $friend_id = $_POST['friend_id'];

    // 處理 SQL 注入攻擊
    $friend_id = mysqli_real_escape_string($connectNow, $friend_id);

    // 查詢用戶資料
    $sql = "SELECT `name`, `photo` FROM `users` WHERE id=?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('s', $friend_id);
        $statement->execute();
        $statement->bind_result($friend_id, $photo);
        $statement->fetch();
        
        if ($friend_id && $photo) {
            // 確保結果集處理完成
            $statement->free_result();
            $statement->close();

            // 檢查用戶是否在 friends 表中且 status 為 false
            $status = 'false'; // 預設值

            $checkFriendSql = "SELECT 1 FROM `friends` WHERE `friend_id` = ? AND `status` = 'false'";
            $checkFriendStatement = $connectNow->prepare($checkFriendSql);

            if ($checkFriendStatement) {
                $checkFriendStatement->bind_param('s', $friend_id);
                $checkFriendStatement->execute();
                $checkFriendStatement->store_result();
                
                if ($checkFriendStatement->num_rows > 0) {
                    $status = 'true'; // 已發送請求
                } else {
                    $status = 'false'; // 未發送請求 或 已為好友
                }

                // 確保結果集處理完成
                $checkFriendStatement->free_result();
                $checkFriendStatement->close();
            } else {
                echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
                exit();
            }

            // 返回成功並包含圖片路徑
            $photoUrl = 'http://192.168.56.1/smiley_backend/img/photo/' . $photo;
            echo json_encode(array(
                "success" => true,
                "name" => $friend_id,
                "photo" => $photoUrl,
                "status" => $status
            ));
        } else {
            // 未找到用戶
            echo json_encode(array("success" => false, "message" => "User not found."));
        }
    } else {
        // 查詢準備失敗
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    // 缺少 id 參數
    echo json_encode(array("success" => false, "message" => "Missing id parameter."));
}

$connectNow->close();
