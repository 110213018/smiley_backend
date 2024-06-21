<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(array("success" => false, "message" => "Invalid request method"));
    exit();
}

// 檢查必需的參數
if (!isset($_POST['user_id']) || !isset($_POST['content']) || !isset($_POST['date'])) {
    echo json_encode(array("success" => false, "message" => "Missing required parameters"));
    exit();
}

// 獲取POST請求中的資料
$user_id = $_POST['user_id'];
$content = $_POST['content'];
$date = $_POST['date'];

// 連接資料庫
include '../connection.php';

// 檢查連接
if (!$connectNow) {
    echo json_encode(array("success" => false, "message" => "Database connection failed"));
    exit();
}

// 插入資料到diaries表
$sql = "INSERT INTO diaries (user_id, content, date) VALUES (?, ?, ?)";
$stmt = $connectNow->prepare($sql);
$stmt->bind_param("iss", $user_id, $content, $date);

if ($stmt->execute()) {
    echo json_encode(array("success" => true, "message" => "日記已成功提交"));
} else {
    echo json_encode(array("success" => false, "message" => "Error: " . $stmt->error));
}

$stmt->close();
$connectNow->close();
