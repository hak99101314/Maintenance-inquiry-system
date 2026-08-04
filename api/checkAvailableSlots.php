<?php
// 啟動 session 並設定回應類型為 JSON
session_start();
header("Content-Type: application/json");

// ====== 資料庫連線設定 ======
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson";
$dbName     = "睿煬企業社";

// 建立連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗"]);
    exit();
}

// ====== 取得使用者送來的日期與時間 ======
$data = json_decode(file_get_contents("php://input"), true);
$date = $data['date'] ?? '';
$time = $data['time'] ?? '';

if (!$date || !$time) {
    echo json_encode(["success" => false, "message" => "缺少日期或時間"]);
    exit();
}

// ====== 設定每個 3 小時區段最多可預約人數 ======
$max_people_per_3hr_slot = 2;

// 將時間字串轉為整點小時，並計算所屬的 3 小時區段
list($hour, $minute) = explode(':', $time);
$hour = intval($hour);
$slot_start_hour = floor($hour / 3) * 3;         // 例如：15:20 → 15
$slot_end_hour = $slot_start_hour + 3;            // 區段結束時間

// 將區段起訖時間格式化為 SQL 用時間字串
$start_time = sprintf('%02d:00:00', $slot_start_hour);  // 如 "15:00:00"
$end_time = sprintf('%02d:00:00', $slot_end_hour);      // 如 "18:00:00"

// ====== 查詢目前這個時間區段的有效預約人數（排除已取消與未到） ======
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND appointment_time >= ? AND appointment_time < ? AND status NOT IN ('cancelled', 'noshow')");
$stmt->bind_param("sss", $date, $start_time, $end_time);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$current_people = $row['total'] ?? 0;
$stmt->close();

// ====== 計算剩餘名額 ======
$available_slots = $max_people_per_3hr_slot - $current_people;

// 回傳 JSON 結果
echo json_encode(["success" => true, "available_slots" => $available_slots]);
$conn->close();
?>