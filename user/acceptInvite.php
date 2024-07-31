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

if (isset($_POST['user_id']) && isset($_POST['friend_id'])) {
    $user_id = $_POST['user_id'];
    $friend_id = $_POST['friend_id'];
    $status = true;

    // 處理 SQL 注入攻擊
    $user_id = mysqli_real_escape_string($connectNow, $user_id);
    $friend_id = mysqli_real_escape_string($connectNow, $friend_id);
    $status = mysqli_real_escape_string($connectNow, $status);

    // 更新 SQL 語句
    $sql = "UPDATE `friends` SET `status`=? WHERE `user_id`=? AND `friend_id`=?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        // 綁定參數
        $statement->bind_param('sss', $status, $user_id, $friend_id);
        $statement->execute();

        // 檢查是否成功更新了一行
        if ($statement->affected_rows > 0) {
            // 返回成功並包含更新後的 name 和 photo
            echo json_encode(array(
                "success" => true,
                "user_id" => $user_id,
                "friend_id" => $friend_id,
                "status" => $status,
            ));
        } else {
            // 如果未更新任何行，可能是找不到對應的用戶
            echo json_encode(array("success" => false, "message" => "User not found or no changes made."));
        }
        $statement->close();
    } else {
        // 查詢準備失敗
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    // 缺少 id、name 或 photo 參數
    echo json_encode(array("success" => false, "message" => "Missing id, name, or photo parameter."));
}

$connectNow->close();
