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

    // 使用 JOIN 從 users 表獲取對應的數據
    $sql = "SELECT posts.id, posts.user_id, posts.text_color, posts.background_color, posts.monster, posts.angel, posts.title, posts.date, posts.content, 
                   users.photo AS user_photo, users.name AS user_name
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE posts.user_id = ? 
            AND posts.date BETWEEN DATE_SUB(CURDATE(), INTERVAL 2 DAY) AND CURDATE() 
            ORDER BY posts.id DESC";  // 今天及過去兩天之間, 按日期降序排列

    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('s', $user_id);
        $statement->execute();
        $result = $statement->get_result();

        $posts = array();
        
        while ($row = $result->fetch_assoc()) {
            $posts[] = array(
                "id" => $row['id'],
                "user_id" => $row['user_id'],
                "text_color" => $row['text_color'],
                "background_color" => $row['background_color'],
                "monster" => $row['monster'],
                "angel" => $row['angel'],
                "title" => $row['title'],
                "date" => $row['date'],
                "content" => $row['content'],
                "user_photo" => $row['user_photo'],
                "user_name" => $row['user_name']
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
    echo json_encode(array("success" => false, "message" => "Missing user_id parameter."));
}

$connectNow->close();
