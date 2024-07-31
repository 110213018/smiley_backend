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

if (isset($_POST['user_id']) && isset($_POST['friend_id'])) {
    $user_id = $_POST['user_id'];
    $friend_id = $_POST['friend_id'];

    // 處理 SQL 注入攻擊
    $user_id = mysqli_real_escape_string($connectNow, $user_id);
    $friend_id = mysqli_real_escape_string($connectNow, $friend_id);

    // 修改為 DELETE 操作
    $sql = "DELETE FROM `friends` WHERE (`user_id` = ? AND `friend_id` = ?) OR (`user_id` = ? AND `friend_id` = ?)";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('ssss', $user_id, $friend_id, $friend_id, $user_id);
        if ($statement->execute()) {
            if ($statement->affected_rows > 0) {
                echo json_encode(array(
                    "success" => true,
                    "message" => "Friendship removed successfully."
                ));
            } else {
                echo json_encode(array("success" => false, "message" => "No friendship found to remove."));
            }
        } else {
            echo json_encode(array("success" => false, "message" => "Execution failed: " . $statement->error));
        }
        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing id parameter."));
}

$connectNow->close();
