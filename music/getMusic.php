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

            $musicEmoDialog = [
                'sadness' => "當外面的雨聲輕輕落下，讓自己放聲哭泣，釋放那些積壓的情緒吧。就像雨後的天空會再度明朗，讓我們相信，陰霾過後總會有晴天。",
                'disgust' => "當你感到厭惡時，試著讓這悠揚的音樂帶走你的不快。讓自己放鬆，轉移注意力，讓音符帶來的安慰讓你的心情變得更輕鬆。",
                'like' => "當心中充滿喜歡與愛意時，讓這首動人的音樂陪伴你，感受愛的美好與甜蜜。讓旋律帶來的愉悅讓你的每一天都充滿溫馨和快樂。",
                'anger' => "當怒火在心中燃燒時，讓這抒情的音樂幫助你平靜下來。深呼吸，讓音符如同微風般撫慰你的心靈，幫助你慢慢平復情緒。",
                'happiness' => "當你感到開心時，讓這首充滿活力的音樂成為你快樂的背景。讓旋律隨著你的笑容一起舞動，把快樂的情緒傳遞到每一個角落。",
                'other' => "當你心情平靜，音樂就像是一抹柔和的顏色，讓它為你的日常生活增添一份輕鬆與愉悅。讓音符伴隨著你的每一天，為生活帶來微妙的美好。",
            ];

            $musicEmoAngMon = [
                'sadness' => 'sadness.png',
                'disgust' => 'disgust.png',
                'like' => 'like.png',
                'anger' => 'anger.png',
                'happiness' => 'happiness.png',
                'other' => 'other.png',
            ];

            $musicPath = isset($musicFiles[$maxEmotion]) ? $musicFiles[$maxEmotion] : $musicFiles['other'];
            $musicDialog = isset($musicEmoDialog[$maxEmotion]) ? $musicEmoDialog[$maxEmotion] : $musicEmoDialog['other'];
            $musicAngMon = isset($musicEmoAngMon[$maxEmotion]) ? $musicEmoAngMon[$maxEmotion] : $musicEmoAngMon['other'];
            echo json_encode(array(
                "success" => true, 
                "music_path" => "http://163.22.32.24/smiley_backend/audio/".$musicPath,
                "music_dialog"=> $musicDialog, 
                "music_photo"=> $musicAngMon, 
                "message" => true
            ));
        } else {
            // 沒有記錄則播放 'other' 的音樂
            echo json_encode(array(
                "success" => true, 
                "music_path" => "http://163.22.32.24/smiley_backend/audio/Creamy.mp3", 
                "music_dialog"=>"當你心情平靜，音樂就像是一抹柔和的顏色，讓它為你的日常生活增添一份輕鬆與愉悅。讓音符伴隨著你的每一天，為生活帶來微妙的美好。",
                "music_photo"=>'other.png',
                "message" => false));
        }

        $statement->close();
    } else {
        echo json_encode(array("success" => false, "message" => "Prepare failed: " . $connectNow->error));
    }
} else {
    echo json_encode(array("success" => false, "message" => "Missing user_id."));
}

$connectNow->close();
