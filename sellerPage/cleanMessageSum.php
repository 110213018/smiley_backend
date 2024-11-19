<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include '../connection.php';

if (!$connectNow) {
    echo json_encode(array("success" => false, "message" => "Database connection failed"));
    exit();
}


if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // 處理 SQL 注入攻擊
    $user_id = mysqli_real_escape_string($connectNow, $user_id);
    // 更新 SQL 語句
    $sql = "UPDATE `messages` SET `is_read`=1 WHERE `sender_id`=?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        // 綁定參數
        $statement->bind_param('s',$user_id);
        $statement->execute();

        // 檢查是否成功更新了一行
        if ($statement->affected_rows > 0) {
            // 返回成功並包含更新後的 name 和 photo
            echo json_encode(array(
                "success" => true
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
