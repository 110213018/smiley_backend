<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include '../connection.php';

if (!$connectNow) {
    echo json_encode(array("success" => false, "message" => "Database connection failed"));
    exit();
}

// $date = date(format: "Y-m-d H:i:s");

// SQL 查詢
$sql = "SELECT 
            sender_id, 
            COUNT(CASE WHEN is_read = 0 THEN 1 END) AS unread_count
        FROM 
            messages
        WHERE 
            sender_id != 76 AND DATE(timestamp) = CURDATE()
        GROUP BY 
            sender_id;";
        
$result = $connectNow->query($sql);

if ($result) {
    $data = array(); // 用於存儲所有行的數據

    // 遍歷結果
    while ($row = $result->fetch_assoc()) {
        $random_num = (string)(((int)$row['sender_id'] % 9 )+1); //頭貼

        $data[] = array(
            "success" => true,
            "random_numbers" => $random_num,
            "sender_id" => $row['sender_id'],
            "unread_count" => $row['unread_count']
        );
    }

    // 返回 JSON 格式數據
    echo json_encode(array("success" => true, "data" => $data));
} else {
    echo json_encode(array("success" => false, "message" => "Query failed: " . $connectNow->error));
}

$connectNow->close();
