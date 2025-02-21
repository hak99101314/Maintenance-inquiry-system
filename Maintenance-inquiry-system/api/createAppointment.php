<?php
header('Content-Type: application/json');
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data['service'] || !$data['date'] || !$data['time']) {
    echo json_encode(['success' => false, 'message' => '參數不完整']);
    exit;
}

$user_id = $_SESSION['user_id'];
$service = $data['service'];
$date = $data['date'];
$time = $data['time'];

$sql = "INSERT INTO appointments (user_id, service, date, time) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $user_id, $service, $date, $time);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '預約成功']);
} else {
    echo json_encode(['success' => false, 'message' => '預約失敗']);
}

$stmt->close();
$conn->close();
?>
