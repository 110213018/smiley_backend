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
    $org_id = $_POST['user_id'];

    // Prevent SQL injection by using prepared statements
    $sql = "SELECT `user_id`, `friend_id` FROM `friends` WHERE (`user_id`=? OR `friend_id`=?) AND `status`=1";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('ss', $org_id, $org_id);
        $statement->execute();
        $result = $statement->get_result();

        if ($result->num_rows > 0) {
            $users = array();
            while ($row = $result->fetch_assoc()) {
                $user_id = $row['user_id'];
                $friend_id = $row['friend_id'];

                $target_id = ($user_id == $org_id) ? $friend_id : $user_id;

                $userSql = "SELECT `name`, `photo` FROM `users` WHERE `id`=?";
                $userStatement = $connectNow->prepare($userSql);

                if ($userStatement) {
                    $userStatement->bind_param('i', $target_id);
                    $userStatement->execute();
                    $userStatement->bind_result($name, $photo);
                    $userStatement->fetch();

                    // ip 要改 192.168.56.1 -> 163.22.32.24
                    if ($name && $photo) {
                        $users[] = array(
                            "id" => (string)$target_id,
                            "name" => $name,
                            "photo" => 'http://163.22.32.24/smiley_backend/img/photo/' . $photo
                        );
                    }
                    $userStatement->close();
                } else {
                    echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
                    exit();
                }
            }
            echo json_encode(array(
                "success" => true,
                "users" => $users
            ));
        } else {
            echo json_encode(array("success" => false, "message" => "User not found."));
        }
        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing id parameter."));
}

$connectNow->close();
