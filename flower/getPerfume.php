<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include '../connection.php';

if (!$connectNow) {
    echo json_encode(array("success" => false, "message" => "Database connection failed"));
    exit();
}

$user_id = $_POST['user_id']; // 假設從前端傳遞 user_id 作為查詢條件

// 從 analysis 表中查詢該用戶的最新一筆情緒數據
$sql = "SELECT angel, monster 
        FROM analysis 
        WHERE user_id = ? 
        ORDER BY id DESC 
        LIMIT 1";

$stmt = $connectNow->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // 獲取情緒數據
    $row = $result->fetch_assoc();

    // 將情緒值存入變數
    $angel_image = $row['angel'];
    $monster_image = $row['monster'];

    $angel = str_replace('angel/', '', $angel_image); // 去掉 "angel/"
    $monster = str_replace('monster/', '', $monster_image); // 去掉 "monster/"

    // 先檢查 angel 是否在 flowers 表的 image 中
    $sql = "SELECT name, image, meaning, perfume 
            FROM flowers 
            WHERE image LIKE CONCAT(?, '%') 
            ORDER BY RAND() 
            LIMIT 1";

    // 檢查 angel
    $stmt = $connectNow->prepare($sql);
    $stmt->bind_param("s", $angel);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 返回隨機選中的花的數據
        $row = $result->fetch_assoc();
        $flower = [
            "status" => "true",
            "name" => $row["name"],
            "image" => $row["image"],
            "meaning" => $row["meaning"],
            "perfume" => $row["perfume"]
        ];
        header('Content-Type: application/json');
        echo json_encode($flower);
    } else {
        // 如果 angel 沒有找到，檢查 monster
        $stmt->close(); // 關閉上一個查詢的準備
        $sql = "SELECT name, image, meaning, perfume 
                FROM flowers 
                WHERE image LIKE CONCAT(?, '%') 
                ORDER BY RAND() 
                LIMIT 1";
                
        $stmt = $connectNow->prepare($sql);
        $stmt->bind_param("s", $monster);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // 返回隨機選中的花的數據
            $row = $result->fetch_assoc();
            $flower = [
                "status" => "true",
                "name" => $row["name"],
                "image" => $row["image"],
                "meaning" => $row["meaning"],
                "perfume" => $row["perfume"]
            ];
            header('Content-Type: application/json');
            echo json_encode($flower);
        } else {
            // 隨機從 flowers 表中選一個
            $stmt->close(); // 關閉上一個查詢的準備
            $sql = "SELECT name, image, meaning, perfume 
                    FROM flowers 
                    ORDER BY RAND() 
                    LIMIT 1";
                    
            $stmt = $connectNow->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // 返回隨機選中的花的數據
                $row = $result->fetch_assoc();
                $flower = [
                    "status" => "true",
                    "name" => $row["name"],
                    "image" => $row["image"],
                    "meaning" => $row["meaning"],
                    "perfume" => $row["perfume"]
                ];
                header('Content-Type: application/json');
                echo json_encode($flower);
            } else {
                // 如果隨機查詢還是沒有找到
                echo json_encode(["status" => "false","error" => "No flowers found"]);
            }
        }
    }
} else {
    echo json_encode(["error" => "No analysis data found for user"]);
}

$stmt->close();
$connectNow->close();
