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

    $sql = "SELECT sadness, disgust, `like`, anger, happiness FROM analysis WHERE user_id = ? ORDER BY id DESC LIMIT 1";
    $statement = $connectNow->prepare($sql);

    if ($statement) {
        $statement->bind_param('s', $user_id);
        $statement->execute();
        $statement->bind_result($sadness, $disgust, $like, $anger, $happiness);
        $statement->fetch();

        // 檢查是否獲取到結果
        if ($sadness !== null && $disgust !== null && $like !== null && $anger !== null && $happiness !== null) {
            // 找到最高的情緒值
            $emotions = [
                'sadness' => $sadness,
                'disgust' => $disgust,
                'like' => $like,
                'anger' => $anger,
                'happiness' => $happiness,
            ];

            arsort($emotions); // 按值進行排序，最大值在前
            $maxEmotion = array_key_first($emotions);
            $musicFiles = [
                'sadness' => 'Rainy.mp3',
                'disgust' => 'RainyDay.mp3',
                'like' => 'Rose.mp3',
                'anger' => 'Thoughtful.mp3',
                'happiness' => 'AppleTree.mp3',
                'other' => 'Creamy.mp3',
            ];

            $musicPath = isset($musicFiles[$maxEmotion]) ? $musicFiles[$maxEmotion] : $musicFiles['other'];
            echo json_encode(array("success" => true, "music_path" => "http://163.22.32.24/smiley_backend/audio/".$musicPath, "message" => true));
        } else {
            // 沒有記錄則播放 'other' 的音樂
            echo json_encode(array("success" => true, "music_path" => "http://163.22.32.24/smiley_backend/audio/Creamy.mp3", "message" => false));
        }

        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing user_id."));
}

$connectNow->close();
