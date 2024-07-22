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

if (isset($_POST['id']) && isset($_POST['name']) && isset($_POST['photo'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $photo = $_POST['photo'];

    // 處理 SQL 注入攻擊
    $id = mysqli_real_escape_string($connectNow, $id);
    $name = mysqli_real_escape_string($connectNow, $name);
    $photo = mysqli_real_escape_string($connectNow, $photo);

    // 更新 SQL 語句
    $sql = "UPDATE `users` SET `name`=?, `photo`=? WHERE `id`=?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        // 綁定參數
        $statement->bind_param('sss', $name, $photo, $id);
        $statement->execute();

        // 檢查是否成功更新了一行
        if ($statement->affected_rows > 0) {
            // 返回成功並包含更新後的 name 和 photo
            echo json_encode(array(
                "success" => true,
                "name" => $name,
                "photo" => $photo
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
