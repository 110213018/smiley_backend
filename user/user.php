<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_log("Received POST data: " . print_r($_POST, true));
error_log("Received FILES data: " . print_r($_FILES, true));

include '../connection.php';

if (!$connectNow) {
    echo json_encode(array("success" => false, "message" => "Database connection failed"));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array("success" => false, "message" => "Invalid request method."));
    exit();
}

if ((isset($_FILES['photo']) || isset($_POST['default_photo'])) && isset($_POST['name']) && isset($_POST['firebase_user_id'])) {
    $friend_id = $_POST['name'];
    $firebase_user_id = $_POST['firebase_user_id'];

    if (isset($_POST['default_photo']) && $_POST['default_photo'] == 'true') {
        $photo = $_POST['photo']; //
        error_log("Using default image");
    } else if (isset($_FILES['photo'])) {
        $photo = $_FILES['photo']['name'];
        $tmp_name = $_FILES['photo']['tmp_name'];
        $imagePath = '../img/photo/' . $photo;

        if (!move_uploaded_file($tmp_name, $imagePath)) {
            echo json_encode(array("success" => false, "message" => "Failed to move uploaded file."));
            exit();
        }
        error_log("Image uploaded to: $imagePath");
    }

    $sql = "INSERT INTO users (firebase_user_id, name, photo) VALUES (?, ?, ?)";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('sss', $firebase_user_id, $friend_id, $photo);
        if ($statement->execute()) {
            $friend_id = $statement->insert_id; // 獲取插入的用戶ID
            echo json_encode(array("success" => true, "user_id" => $friend_id, "photo" => $photo,));
        } else {
            echo json_encode(array("success" => false, "message" => "Execute failed: " . $statement->error));
        }
        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing required data."));
}

$connectNow->close();
