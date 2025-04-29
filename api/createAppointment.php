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
$dbPassword = "karry,roy,jackson";
$dbName     = "睿煬企業社";

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗: " . $conn->connect_error]);
    exit();
}

// 從 Session 取得使用者 ID
$customer_id = $_SESSION['user_id'];

// 查詢該車輛的 ID
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

// ======= 檢查 3 小時區間人數 =======
$max_people_per_3hr_slot = 2;
list($hour, $minute) = explode(':', $time);
$hour = intval($hour);

$slot_start_hour = floor($hour / 3) * 3;
$slot_end_hour = $slot_start_hour + 3;

$start_time = sprintf('%02d:00:00', $slot_start_hour);
$end_time = sprintf('%02d:00:00', $slot_end_hour);

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = ? AND appointment_time >= ? AND appointment_time < ?");
$stmt->bind_param("sss", $date, $start_time, $end_time);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$current_people = $row['total'];
$stmt->close();

if ($current_people >= $max_people_per_3hr_slot) {
    echo json_encode(["success" => false, "message" => "此3小時時段預約人數已滿，請選擇其他時間"]);
    $conn->close();
    exit();
}

// ======= 寫入預約資料 =======
$stmt = $conn->prepare("INSERT INTO appointments (customer_id, vehicle_id, appointment_date, appointment_time, service_items) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $customer_id, $vehicle_id, $date, $time, $service);

if ($stmt->execute()) {
    // ✅ 預約成功，寄送通知信
    require_once '../send_email.php'; // 引入寄信功能

    // 取得使用者 email 與姓名
    $stmt_user = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
    $stmt_user->bind_param("i", $customer_id);
    $stmt_user->execute();
    $user_result = $stmt_user->get_result();
    $user = $user_result->fetch_assoc();
    $stmt_user->close();

    if ($user) {
        $to_email = $user['email'];
        $to_name = $user['full_name'];

        $subject = "預約成功通知 - 睿煬企業社";
        $body = "
            親愛的 {$to_name} 您好，<br><br>
            恭喜您已成功完成預約，以下是您的預約資訊：<br><br>
            ■ 車牌號碼：{$license_plate}<br>
            ■ 預約日期：{$date}<br>
            ■ 預約時間：{$time}<br>
            ■ 服務項目：{$service}<br><br>
            請依照預約時間準時到店，感謝您的支持與信任！<br><br>
            — 睿煬企業社 敬上
        ";

        sendEmail($to_email, $to_name, $subject, $body);
    }

    echo json_encode(["success" => true, "message" => "預約成功並已寄出通知信！"]);
} else {
    echo json_encode(["success" => false, "message" => "預約失敗，請稍後再試"]);
}

$stmt->close();
$conn->close();
?>
