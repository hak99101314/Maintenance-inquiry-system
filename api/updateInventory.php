<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '無效的請求方法']);
    exit;
}

// 資料庫連接
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
$partName = $data['partName'];
$quantity = $data['quantity'];

if (!isset($partName, $quantity)) {
    echo json_encode(['success' => false, 'message' => '請提供零件名稱和數量']);
    exit;
}

$sql = "UPDATE inventory SET quantity = ? WHERE part_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $quantity, $partName);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '庫存更新失敗']);
}

$stmt->close();
$conn->close();
?>
