<?php
session_start();
header("Content-Type: application/json");

// 資料庫設定
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson"; // 這邊改你自己的
$dbName     = "睿煬企業社";

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗"]);
    exit();
}

// 接收前端傳來的日期和時間
$data = json_decode(file_get_contents("php://input"), true);
$date = $data['date'] ?? '';
$time = $data['time'] ?? '';

if (!$date || !$time) {
    echo json_encode(["success" => false, "message" => "缺少日期或時間"]);
    exit();
}

// 設定每 3 小時最多 2人
$max_people_per_3hr_slot = 2;

// 把時間轉成 3小時區段
list($hour, $minute) = explode(':', $time);
$hour = intval($hour);
$slot_start_hour = floor($hour / 3) * 3;
$slot_end_hour = $slot_start_hour + 3;

$start_time = sprintf('%02d:00:00', $slot_start_hour);
$end_time = sprintf('%02d:00:00', $slot_end_hour);

// 查詢當天這3小時內的預約數量
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND appointment_time >= ? AND appointment_time < ?");
$stmt->bind_param("sss", $date, $start_time, $end_time);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$current_people = $row['total'];
$stmt->close();

// 計算剩餘名額
$available_slots = $max_people_per_3hr_slot - $current_people;

echo json_encode(["success" => true, "available_slots" => $available_slots]);
$conn->close();
?>
