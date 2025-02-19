<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$plateNumber = $data['plateNumber'] ?? '';
$phoneNumber = $data['phoneNumber'] ?? '';
$repairDate = $data['repairDate'] ?? '';
$description = $data['description'] ?? '';
$cost = $data['cost'] ?? '';

if (empty($plateNumber) || empty($phoneNumber) || empty($repairDate) || empty($description) || empty($cost)) {
    echo json_encode(['success' => false, 'message' => '請填寫完整的維修資料']);
    exit;
}

$sql = "INSERT INTO maintenance_records (plate_number, phone_number, date, description, cost)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssd", $plateNumber, $phoneNumber, $repairDate, $description, $cost);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '無法新增維修紀錄']);
}

$stmt->close();
$conn->close();
?>
