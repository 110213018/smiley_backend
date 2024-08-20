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

    // 防止 SQL 注入
    $user_id = mysqli_real_escape_string($connectNow, $user_id);

    $sql = "SELECT `text_color`, `background_color`, `monster`, `angel`, `title`, `date`, `content` 
            FROM `posts` 
            WHERE user_id=? 
            AND `date` BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 DAY) AND CURDATE() 
            ORDER BY `id` DESC";  // // 今天及過去兩天之間 ,按日期降序排列

    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('s', $user_id);
        $statement->execute();
        $result = $statement->get_result();

        $posts = array();
        
        while ($row = $result->fetch_assoc()) {
            $posts[] = array(
                "text_color" => $row['text_color'],
                "background_color" => $row['background_color'],
                "monster" => $row['monster'],
                "angel" => $row['angel'],
                "title" => $row['title'],
                "date" => $row['date'],
                "content" => $row['content'],
            );
        }
        
        if (!empty($posts)) {
            // 返回多個貼文的 JSON
            echo json_encode(array("success" => true, "posts" => $posts));
        } else {
            echo json_encode(array("success" => false, "message" => "No posts found for the given user and date."));
        }

        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing user_id or date parameter."));
}

$connectNow->close();
