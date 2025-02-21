<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "睿煬企業社";

$data = json_decode(file_get_contents('php://input'), true);

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

// 取得傳入的資料
$user_id = $data['user_id'] ?? '';
$vehicle_id = $data['vehicle_id'] ?? '';
$repair_date = $data['repair_date'] ?? '';
$repair_content = $data['repair_content'] ?? '';
$repair_cost = $data['repair_cost'] ?? 0;

if (empty($user_id) || empty($vehicle_id) || empty($repair_date)) {
    echo json_encode(['success' => false, 'message' => '請填寫完整資料']);
    exit;
}

$sql = "INSERT INTO maintenance_records (vehicle_id, date, content, cost, staff_id) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issdi", $vehicle_id, $repair_date, $repair_content, $repair_cost, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '維修紀錄已成功新增']);
} else {
    echo json_encode(['success' => false, 'message' => '新增失敗: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
