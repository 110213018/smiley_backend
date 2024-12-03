<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include '../connection.php';

if (!$connectNow) {
    echo json_encode(["status" => "false", "message" => "Database connection failed"]);
    exit();
}

$user_id = $_POST['user_id']; // 假設從前端傳遞 user_id 作為查詢條件
$today = date("Y-m-d");

// 查詢分析表中該用戶的最新情緒數據
// Fetching the latest emotional data
$sql = "SELECT sadness, disgust, `like`, anger, happiness, other, angel, monster FROM analysis WHERE user_id = ? ORDER BY date DESC LIMIT 1";
$stmt = $connectNow->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Image paths based on angel and monster
    $angel_image = str_replace('angel/', '', $row['angel']);
    $monster_image = str_replace('monster/', '', $row['monster']);
    $angel = explode('_', $angel_image)[0]; // Without extension
    $monster = explode('_', $monster_image)[0];

    // Logic for determining which flower to retrieve
    if ($row[$angel] > $row[$monster]){
        $flower = findFlower($connectNow, $angel, $angel_image);
    } elseif ($row[$angel] < $row[$monster]) {
        $flower = findFlower($connectNow, $monster, $monster_image);
    } else {
        $angel_or_monster = rand(0, 1) ? $angel : $monster;
        $angel_or_monster_image = $angel_or_monster . '.png'; // Corrected string concatenation
        $flower = findFlower($connectNow, $angel_or_monster, $angel_or_monster_image);
    }

    $sql = "INSERT INTO sentiment_report (`user_id`,`date`,`oil_name`, `oil_image`, `oil_effect`, `oil_usage`) VALUE (?,?,?,?,?,?)";
    $stmt = $connectNow->prepare($sql);
    $stmt->bind_param("ssssss", $user_id,$today,$flower["name"],$flower["image"],$flower["meaning"],$flower["perfume"]);

    // Check if flower data exists
    if ($flower && $stmt->execute()) {
        echo json_encode([
            "status" => "true",
            "user_id" => $user_id,
            "date" => $today,
            "name" => $flower["name"],
            "image" => $flower["image"],
            "meaning" => $flower["meaning"],
            "perfume" => $flower["perfume"]
        ]);
    } else {
        echo json_encode(["status" => "false", "message" => "No flowers found"]);
    }
} else {
    echo json_encode(["status" => "false", "message" => "No analysis data found for user"]);
}

$stmt->close();
$connectNow->close();


/**
 * 根據花名查詢 flower 表中的數據
 */
function findFlower($db, $emotionName, $emotionImage)
{
    // echo json_encode("emotionName= $emotionName, emotionImage= $emotionImage");
    $sql = "SELECT name, image, meaning, perfume FROM flowers WHERE image =?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $emotionImage);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row1 = $result->fetch_assoc();
        $sql = "SELECT flower_emotion,effect,method FROM oil_feedbacks WHERE flower_emotion=?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $emotionName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row2 = $result->fetch_assoc();
            return [
                "name" => $row1["name"],
                "image" => $row1["image"],
                "meaning" => $row2["effect"],
                "perfume" => $row2["method"]
            ];
        }
        return null;
    }else{
        if ($emotionName == "happiness"){
            return [
                "name" => "薰衣草",
                "image" => "happiness_5.png",
                "meaning" => "< 緩解焦慮、舒緩壓力、恢復精力 >\n在壓力導致失眠的情況下，其強大的鎮靜效果著稱，且有助於平衡神經系統，可以幫助人們在壓力下放鬆心情，減少壓力感，使人在疲憊時感覺更有精神和舒適。",
                "perfume" => "< 推薦使用方式 - 擴香 >\n用途：放鬆身心、減壓、助眠\n方法：使用擴香器或加濕器，加入3-5滴精油到水中，讓精油的香氣隨著蒸汽散發到空氣中。"
            ];
        }elseif($emotionName == "like"){
            return [
                "name" => "玫瑰",
                "image" => "like_3.png",
                "meaning" => "<甜美、浪漫、幸福>\n具有極強的情感療癒效果，幫助增強愛與被愛的感覺。它的香氣能激發內心的甜美與浪漫，讓使用者感受到幸福與愛的美好，尤其適合在喜愛的情緒中使用。",
                "perfume" => "<推薦使用方式 - 香氛噴霧>\n用途：增強愛的情感、提升幸福感、營造浪漫氛圍\n方法：將3-5滴精油加入50ml蒸餾水，裝入噴霧瓶中，隨時噴灑於房間或衣物上，讓香氣隨著空氣蔓延，提升愉悅感。"
            ];
        }elseif($emotionName == "disgust"){
            return [
                "name" => "薄荷",
                "image" => "disgust_2.png",
                "meaning" => "<淨化心靈、提振精神>\n其清新的氣息著稱，能有效驅散令人不愉快的感覺，讓人感到煥然一新。同時具有淨化心靈與空間的作用，幫助轉換負面情緒。",
                "perfume" => "<推薦使用方式 - 空氣淨化>\n用途：驅除負能量、提振心情、淨化空間\n方法：將3-5滴精油加入擴香器中，或滴在棉球上放置於房間四角，營造乾淨、清新的氛圍，驅走不愉快的感覺。"
            ];
        }elseif($emotionName == "sadness"){
            return [
                "name" => "肖楠",
                "image" => "sadness_6.png",
                "meaning" => "< 緩解悲傷、舒緩情緒 >\n其清新的木質香氣能幫助舒緩情緒，減輕內心的悲傷感。它具有強大的情緒調節作用，能為心靈提供安慰，讓使用者感覺更有力量來面對困難的情緒。",
                "perfume" => "< 推薦使用方式 - 香氛按摩 >\n用途：釋放悲傷、安撫心靈、增強內心力量\n方法：將2-3滴精油混合15ml基底油（如甜杏仁油），輕柔地按摩肩頸和手腕，讓香氣緩慢地散發，安撫緊繃的情緒，幫助釋放悲傷。"
            ];
        }elseif($emotionName == "anger"){
            return [
                "name" => "馬告",
                "image" => "anger_3.png",
                "meaning" => "<平復情緒波動、冷靜>\n辛香氣息具有強烈的放鬆效果，能有效減少情緒波動，平復因憤怒所帶來的心理壓力。它對於釋放情緒及促進冷靜與理智的回歸有顯著的效果。",
                "perfume" => "<推薦使用方式 - 熱敷>\n用途：平復怒氣、釋放壓力、促進情緒冷靜\n方法：將5滴精油滴入溫水中，浸濕毛巾後輕擰乾，敷在額頭或脖後，靜靜地深呼吸，幫助冷靜情緒並釋放怒氣。"
            ];
        }else{
            return [
                "name" => "乳香",
                "image" => "other_3.png",
                "meaning" => "<放鬆、身心平衡>\n其以平衡與穩定的特性著稱，適合用於日常放鬆或不明確的情緒狀態，幫助人們重拾心靈的平靜與專注。",
                "perfume" => "< 推薦使用方式 - 擴香 >\n用途：放鬆身心、減壓、助眠\n方法：使用擴香器或加濕器，加入3-5滴精油到水中，讓精油的香氣隨著蒸汽散發到空氣中。"
            ];
        }
    }
}
