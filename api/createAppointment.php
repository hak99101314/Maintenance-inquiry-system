<?php
session_start();
header("Content-Type: application/json");

// 必須先登入才能預約
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "請先登入後預約"]);
    exit();
}

// 取得前端送來的 JSON 資料
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["success" => false, "message" => "無效的資料格式"]);
    exit();
}

// 取得表單欄位值
$name          = $data['name'] ?? '';
$phone         = $data['phone'] ?? '';
$license_plate = $data['license_plate'] ?? '';
$service       = $data['service'] ?? '';
$date          = $data['date'] ?? '';
$time          = $data['time'] ?? '';

if (!$name || !$phone || !$license_plate || !$service || !$date || !$time) {
    echo json_encode(["success" => false, "message" => "請填寫完整資料"]);
    exit();
}

// 資料庫設定
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson"; //←這裡我已經依你的指示改了
$dbName     = "睿煬企業社";

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗: " . $conn->connect_error]);
    exit();
}

// 從 Session 取得使用者 ID
$customer_id = $_SESSION['user_id'];

// 取得該車輛的 ID
$stmt = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE license_plate = ? AND owner_id = ?");
$stmt->bind_param("si", $license_plate, $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $vehicle_id = $row['vehicle_id'];
} else {
    echo json_encode(["success" => false, "message" => "車輛資料不存在，請先新增車輛"]);
    exit();
}
$stmt->close();

// ======= 檢查黑名單與 no-show 限制 =======

// 1. 是否已被加入黑名單
$stmt = $conn->prepare("SELECT * FROM appointment_blacklist WHERE user_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "您已被列入黑名單，無法進行預約，如有問題請您聯絡管理員"]);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// 2. 是否已達 No-Show 次數上限
$stmt = $conn->prepare("SELECT no_show_count FROM users WHERE user_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($result && $result['no_show_count'] >= 3) {
    // 自動列入黑名單
    $reason = "預約未到次數達 3 次";
    $stmt = $conn->prepare("INSERT INTO appointment_blacklist (user_id, reason) VALUES (?, ?)");
    $stmt->bind_param("is", $customer_id, $reason);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => false, "message" => "您已達預約未到次數上限，系統已自動將您列入黑名單，如有問題，請您聯絡管理員"]);
    $conn->close();
    exit();
}

// ======= 【新增】檢查 3 小時區間人數限制 =======

// 設定每個 3 小時區段最多允許的人數
$max_people_per_3hr_slot = 2;

// 把使用者輸入的時間字串（例如 '15:00'）轉成小時數字
list($hour, $minute) = explode(':', $time);
$hour = intval($hour);

// 計算屬於哪個3小時區間
$slot_start_hour = floor($hour / 3) * 3;   // 例如 15:00 就是 15
$slot_end_hour = $slot_start_hour + 3;      // 結束時間是 +3小時（不含）

// 把時間區間轉成時間字串
$start_time = sprintf('%02d:00:00', $slot_start_hour);  // 例如 "15:00:00"
$end_time = sprintf('%02d:00:00', $slot_end_hour);      // 例如 "18:00:00"

// 查詢：當天，這個3小時區間內的預約數
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND appointment_time >= ? AND appointment_time < ?");
$stmt->bind_param("sss", $date, $start_time, $end_time);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$current_people = $row['total'];
$stmt->close();

// 如果超過人數上限
if ($current_people >= $max_people_per_3hr_slot) {
    echo json_encode(["success" => false, "message" => "此3小時時段預約人數已滿，請選擇其他時間"]);
    $conn->close();
    exit();
}


// ======= 寫入預約資料 =======

// 寫入新的預約紀錄
$stmt = $conn->prepare("INSERT INTO appointments (customer_id, vehicle_id, appointment_date, appointment_time, service_items) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $customer_id, $vehicle_id, $date, $time, $service);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "預約成功！"]);
} else {
    echo json_encode(["success" => false, "message" => "預約失敗，請稍後再試"]);
}

$stmt->close();
$conn->close();
?>
