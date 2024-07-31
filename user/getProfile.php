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

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // 處理 SQL 注入攻擊
    $id = mysqli_real_escape_string($connectNow, $id);

    $sql = "SELECT `name`, `photo` FROM `users` WHERE id=?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('s', $id);
        $statement->execute();
        $statement->bind_result($name, $photo);
        $statement->fetch();
        
        if ($name && $photo) {
            // 返回成功並包含圖片路徑
            $photoUrl = 'http://192.168.56.1/smiley_backend/img/photo/' . $photo;
            echo json_encode(array(
                "success" => true,
                "name" => $name,
                "photo" => $photoUrl
            ));
        } else {
            // 未找到用戶
            echo json_encode(array("success" => false, "message" => "User not found."));
        }
        $statement->close();
    } else {
        // 查詢準備失敗
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    // 缺少 id 參數
    echo json_encode(array("success" => false, "message" => "Missing id parameter."));
}

$connectNow->close();
