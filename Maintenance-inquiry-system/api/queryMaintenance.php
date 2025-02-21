<?php
header('Content-Type: application/json');

// 資料庫連接資訊
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

// 接收前端傳來的 JSON 資料
$data = json_decode(file_get_contents('php://input'), true);
$plateNumber = $data['plateNumber'] ?? '';
$phoneNumber = $data['phoneNumber'] ?? '';

if (empty($plateNumber) || empty($phoneNumber)) {
    echo json_encode(['success' => false, 'message' => '請提供車牌號碼與電話號碼']);
    exit;
}

// 查詢維修紀錄
$stmt = $conn->prepare("
    SELECT mr.date, mr.type, mr.content, mr.cost
    FROM maintenance_records mr
    INNER JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
    INNER JOIN users u ON v.owner_id = u.user_id
    WHERE v.license_plate = ? AND u.contact_number = ?
");
$stmt->bind_param("ss", $plateNumber, $phoneNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    echo json_encode(['success' => true, 'records' => $records]);
} else {
    echo json_encode(['success' => false, 'message' => '未找到維修紀錄']);
}

$stmt->close();
$conn->close();
?>
