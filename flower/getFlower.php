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
$date = date("Y-m-d");

// 將日期轉換為數字來作為隨機種子
srand(strtotime($date));

// 從資料庫中隨機選擇一個花
$sql = "SELECT name, image, meaning, perfume FROM flowers ORDER BY RAND() LIMIT 1";
$result = $connectNow->query($sql);


if ($result->num_rows > 0) {
    // 輸出數據
    $row = $result->fetch_assoc();
    $flower = [
        "name" => $row["name"],
        "image" => $row["image"],
        "meaning" => $row["meaning"],
        "perfume" => $row["perfume"]
    ];
    header('Content-Type: application/json');
    echo json_encode($flower);
} else {
    echo json_encode(["error" => "No flowers found"]);
}

$connectNow->close();
