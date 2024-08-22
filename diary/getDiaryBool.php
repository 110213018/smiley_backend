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
    $date = $_POST['date'];

    $sql = "SELECT * FROM diaries WHERE user_id = ? AND date=?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('ss', $user_id, $date);
        $statement->execute();
        // 獲取查詢結果
        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            // 搜索到日記
            echo json_encode(array("success" => true, "diary_bool" => true));
        } else {
            // 沒有找到日記
            echo json_encode(array("success" => true, "diary_bool" => false, "message" => "No diary found."));
        }
        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing firebase_user_id."));
}

$connectNow->close();