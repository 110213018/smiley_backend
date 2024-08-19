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

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // 處理 SQL 注入攻擊
    $user_id = mysqli_real_escape_string($connectNow, $user_id);

    $sql = "SELECT `content`, `date` FROM `diaries` WHERE user_id=?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('s', $user_id);
        $statement->execute();
        $statement->bind_result($content, $date);

        $diaries = array();

        while ($statement->fetch()) {
            // 將日期作為鍵，將日記內容存儲在鍵值對中
            $diaries[$date] = array(
                'diary' => array(
                    'content' => $content
                )
            );
        }
        
        if (!empty($diaries)) {
            // 返回多個日記的 JSON 結構
            echo json_encode(array("success" => true, "diaries" => $diaries));
        } else {
            // 未找到用戶日記
            echo json_encode(array("success" => false, "message" => "No diaries found"));
        }

        $statement->close();
    } else {
        // 查詢準備失敗
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    // 缺少 user_id 參數
    echo json_encode(array("success" => false, "message" => "Missing user_id parameter."));
}

$connectNow->close();
