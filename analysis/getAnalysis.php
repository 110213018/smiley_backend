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

if (isset($_POST['user_id']) && isset($_POST['date'])) {
    $user_id = $_POST['user_id'];
    $date = $_POST['date']; // 2024-08-04

    // 處理 SQL 注入攻擊
    $user_id = mysqli_real_escape_string($connectNow, $user_id);
    $date = mysqli_real_escape_string($connectNow, $date);

    $sql = "SELECT `sadness`, `disgust`, `like`, `anger`, `happiness`, `other` FROM `analysis` WHERE user_id=? AND date=?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('ss',$user_id, $date);
        $statement->execute();
        $statement->bind_result($sadness, $disgust, $like, $anger, $happiness, $other);
        $statement->fetch();
        
        if ($user_id && $date) {
            echo json_encode(array(
                'success' => true,
                'happiness' => $happiness,
                'like' => $like,
                'sadness' => $sadness,
                'disgust' => $disgust,
                'anger' => $anger,
            ));
        } else {
            // 未找到用戶
            echo json_encode(array("success" => false, "message" => "analysis result not found."));
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
