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

if (isset($_POST['firebase_user_id'])) {
    $firebase_user_id = $_POST['firebase_user_id'];

    $sql = "SELECT id FROM users WHERE firebase_user_id = ?";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('s', $firebase_user_id);
        $statement->execute();
        $statement->bind_result($user_id);
        $statement->fetch();
        
        if ($user_id) {
            echo json_encode(array("success" => true, "user_id" => $user_id));
        } else {
            echo json_encode(array("success" => false, "message" => "User not found."));
        }
        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing firebase_user_id."));
}

$connectNow->close();