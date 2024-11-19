<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include '../connection.php';

if (!$connectNow) {
    echo json_encode(array("success" => false, "message" => "Database connection failed"));
    exit();
}

// 取得當前日期
$user_id = $_POST['user_id'];
$date = date(format: "Y-m-d");

// 從資料庫中隨機選擇一個花
$sql = "SELECT oil_name, oil_image, oil_effect, oil_usage FROM sentiment_report WHERE user_id= ? AND `date`=?";
$statement = $connectNow->prepare($sql);
$statement->bind_param('ss', $user_id,$date);


if ($statement->execute()) {
    // 輸出數據
    $statement->bind_result($oilName, $oilImage,$oilEffect,$oilUsage);
    $statement->fetch();
    echo json_encode(array(
        "success" => true,
        "oil_name" => $oilName,
        "oil_image" => $oilImage,
        "oil_effect" => $oilEffect,
        "oil_usage" => $oilUsage,
    ));
} else {
    echo json_encode(["error" => "No flowers found"]);
}

$connectNow->close();
