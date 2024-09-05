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
    $post_id = $_POST['post_id'];

    $sql = "SELECT COUNT(id) FROM comments WHERE post_id = ? AND pos != 0 AND content != '' ";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('s',$post_id);
        $statement->execute();
        $statement->bind_result($count);
        $statement->fetch();
        
        echo json_encode(array("success" => true, "count" => $count));
        
        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing firebase_user_id."));
}

$connectNow->close();
