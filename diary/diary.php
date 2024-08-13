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

if (isset($_POST['user_id']) && isset($_POST['content']) && isset($_POST['date'])) {
    $user_id = $_POST['user_id'];
    $content = $_POST['content'];
    $date = $_POST['date'];


    // 處理 SQL 注入攻擊
    $user_id = mysqli_real_escape_string($connectNow, $user_id);
    $content = mysqli_real_escape_string($connectNow, $content);
    $date = mysqli_real_escape_string($connectNow, $date);

    // 更新 SQL 語句
    $sql = "INSERT INTO diaries (user_id, content, date) VALUES (?, ?, ?)";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        // 綁定參數
        $statement->bind_param("sss", $user_id, $content, $date); 
        if ($statement->execute()) {
            // 獲取插入的 `diary_id` (53 的 insert.id 改成 insert_id)
            $diary_id = $statement->insert_id;
            $command = escapeshellcmd("python3 C:\114project\smiley_backend\predict.py" . $diary_id);
            $output = shell_exec($command);

            echo json_encode(array(
                "success" => true,
                "message" => "diary submit success"
                // 在這裡加要回傳到前端的東西~
            ));
        } else {
            echo json_encode(array("success" => false, "message" => "Execute failed: " . $statement->error));
        }
        $statement->close();
    } else {
        // 查詢準備失敗
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing parameter."));
}

$connectNow->close();
