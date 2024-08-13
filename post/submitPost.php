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

if (isset($_POST['user_id']) && isset($_POST['color']) && isset($_POST['monster']) && isset($_POST['angel']) && isset($_POST['title']) && isset($_POST['date']) && isset($_POST['content'])) {
    $user_id = $_POST['user_id'];
    $color = $_POST['color'];
    $monster = $_POST['monster'];
    $angel = $_POST['angel'];
    $title = $_POST['title'];
    $date = $_POST['date'];
    $content = $_POST['content'];

    // 處理 SQL 注入攻擊
    $user_id = mysqli_real_escape_string($connectNow, $user_id);
    $color = mysqli_real_escape_string($connectNow, $color);
    $monster = mysqli_real_escape_string($connectNow, $monster);
    $angel = mysqli_real_escape_string($connectNow, $angel);
    $title = mysqli_real_escape_string($connectNow, $title);
    $date = mysqli_real_escape_string($connectNow, $date);
    $content = mysqli_real_escape_string($connectNow, $content);

    // 更新 SQL 語句
    $sql = "INSERT INTO posts (user_id, color, monster, angel, title, date, content) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        // 綁定參數
        $statement->bind_param('sssssss', $user_id, $color, $monster, $angel, $title, $date, $content);
        if ($statement->execute()) {
            echo json_encode(array("success" => true, "message" => "submitPost success"));
        } else {
            echo json_encode(array("success" => false, "message" => "Execute failed: " . $statement->error));
        }
        $statement->close();
    } else {
        // 查詢準備失敗
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    // 缺少 id、name 或 photo 參數
    echo json_encode(array("success" => false, "message" => "Missing id, name, or photo parameter."));
}

$connectNow->close();
