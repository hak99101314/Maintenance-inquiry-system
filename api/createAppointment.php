<?php
session_start();
header("Content-Type: application/json");

// ✅ 驗證是否登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "請先登入後預約"]);
    exit();
}

// ✅ 接收前端 JSON 請求資料
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["success" => false, "message" => "無效的資料格式"]);
    exit();
}

// ✅ 取得欄位值
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

// ✅ 資料庫連線
$conn = new mysqli("localhost", "root", "", "睿煬企業社");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗: " . $conn->connect_error]);
    exit();
}

$customer_id = $_SESSION['user_id'];

// ✅ 查詢該車是否屬於登入會員
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

// ✅ 檢查黑名單限制
$stmt = $conn->prepare("SELECT 1 FROM appointment_blacklist WHERE user_id = ?");
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

// ✅ 檢查 no_show 次數達上限 → 自動加入黑名單
$stmt = $conn->prepare("SELECT no_show_count FROM users WHERE user_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($result && $result['no_show_count'] >= 3) {
    $reason = "預約未到次數達 3 次";
    $stmt = $conn->prepare("INSERT INTO appointment_blacklist (user_id, reason) VALUES (?, ?)");
    $stmt->bind_param("is", $customer_id, $reason);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => false, "message" => "您已達預約未到次數上限，系統已自動將您列入黑名單，如有問題，請您聯絡管理員"]);
    $conn->close();
    exit();
}

// ✅ 檢查 3 小時區段預約數限制（最多 2 人）
$max_people_per_3hr_slot = 2;
list($hour, $minute) = explode(':', $time);
$hour = intval($hour);
$slot_start_hour = floor($hour / 3) * 3;
$slot_end_hour = $slot_start_hour + 3;
$start_time = sprintf('%02d:00:00', $slot_start_hour);
$end_time   = sprintf('%02d:00:00', $slot_end_hour);

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND appointment_time >= ? AND appointment_time < ? AND status NOT IN ('cancelled', 'noshow')");
$stmt->bind_param("sss", $date, $start_time, $end_time);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$current_people = $row['total'] ?? 0;
$stmt->close();

if ($current_people >= $max_people_per_3hr_slot) {
    echo json_encode(["success" => false, "message" => "此3小時時段預約人數已滿，請選擇其他時間"]);
    $conn->close();
    exit();
}

// ✅ 寫入預約資料
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