<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['appointment_id'])) {
    echo json_encode(['success' => false, 'message' => '缺少 appointment_id']);
    exit;
}

$appointment_id = $data['appointment_id'];
$conn = new mysqli("localhost", "root", "karry,roy,jackson", "睿煬企業社");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連線失敗']);
    exit;
}

// 查詢 appointment 對應的用戶與車牌
$stmt = $conn->prepare("
    SELECT a.customer_id, v.license_plate
    FROM appointments a
    JOIN vehicles v ON a.vehicle_id = v.vehicle_id
    WHERE a.appointment_id = ?
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$info = $result->fetch_assoc();
$stmt->close();

if (!$info) {
    echo json_encode(['success' => false, 'message' => '找不到預約資訊']);
    exit;
}

$member_id = $info['customer_id'];
$license_plate = $info['license_plate'];

// 更新預約狀態為 repair
$stmt = $conn->prepare("UPDATE appointments SET status = 'repair' WHERE appointment_id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$stmt->close();

// 建立估價單（空白項目）
$stmt = $conn->prepare("INSERT INTO estimates (member_id, license_plate, status, created_at) VALUES (?, ?, 'pending', NOW())");
$stmt->bind_param("is", $member_id, $license_plate);
$stmt->execute();
$estimate_id = $stmt->insert_id;
$stmt->close();

$conn->close();
echo json_encode([
    'success' => true,
    'license_plate' => $license_plate,
    'estimate_id' => $estimate_id
]);
