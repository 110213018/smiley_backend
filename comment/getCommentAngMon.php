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

if (isset($_POST['post_id'])) {
    $id = $_POST['post_id'];

    // 處理 SQL 注入攻擊
    $id = mysqli_real_escape_string($connectNow, $id);

    $sql = "SELECT id, text_color, background_color, monster, angel, title, date, content FROM `posts` WHERE id=?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('s', $id);
        $statement->execute();
        $statement->bind_result($id, $text_color, $background_color, $monster, $angel,$title, $date, $content);
        $statement->fetch();
        
        // ip 要改 192.168.56.1 -> 163.22.32.24
        if ($angel || $monster) {
            if ($angel == null){
                $image = 'http://163.22.32.24/smiley_backend/img/monster/' . $monster;
            }else{
                $image = 'http://163.22.32.24/smiley_backend/img/angel/' . $angel;
            }
        
            echo json_encode(array(
                "success" => true,
                "id" => $id,
                "image" => $image,
                "text_color"=> $text_color,
                "background_color"=> $background_color,
                "title"=> $title,
                "date"=> $date,
                "content"=> $content,
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
