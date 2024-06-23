<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// 檢查是否為 POST 請求
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405); // 設置 HTTP 狀態碼為 405 Method Not Allowed
    echo json_encode(array("success" => false, "message" => "Invalid request method"));
    // 輸出：
    // {
    //     "success": false,
    //     "message": "Invalid request method"
    // }
    exit();
}

// 檢查必需的參數
// isset 檢查該值是否沒被定義或為空
if (!isset($_POST['user_id']) || !isset($_POST['content']) || !isset($_POST['date'])) {
    http_response_code(400); // 設置 HTTP 狀態碼為 400 Bad Request
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
    http_response_code(500); // 設置 HTTP 狀態碼為 500 Internal Server Error
    echo json_encode(array("success" => false, "message" => "Database connection failed"));
    exit();
}

// 插入資料到diaries表
$sql = "INSERT INTO diaries (user_id, content, date) VALUES (?, ?, ?)"; // 定義 SQL 插入語句，使用 ? 作為佔位符
$stmt = $connectNow->prepare($sql); // 把準備好的 SQL 指令和 MySQL 資料庫連結，創建一個預準備語句

if ($stmt){
    $stmt->bind_param("iss", $user_id, $content, $date); // 綁定參數到 SQL 指令中的佔位符，"iss" 表示參數類型分別為 integer 和 string

    // 嘗試執行預準備語句並檢查結果
    if ($stmt->execute()) {
        // 如果執行成功，返回成功的 JSON 響應
        http_response_code(200); // 設置 HTTP 狀態碼為 200 OK
        echo json_encode(array("success" => true, "message" => "日記已成功提交"));
    } else {
        // 如果執行失敗，返回錯誤的 JSON 響應，並包含錯誤訊息
        http_response_code(500); // 設置 HTTP 狀態碼為 500 Internal Server Error
        echo json_encode(array("success" => false, "message" => "Error: " . $stmt->error));
    }

    // 釋放預準備語句資源
    $stmt->close();
}else{
    http_response_code(500); // 設置 HTTP 狀態碼為 500 Internal Server Error
    echo json_encode(array("success" => false, "message" => "Failed to prepare statement: " . $connectNow->error));
}
// 關閉資料庫連接
$connectNow->close();

