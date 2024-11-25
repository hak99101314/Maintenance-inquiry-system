<?php
header('Content-Type: application/json');
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
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

$user_id = $_SESSION['user_id'];

// 更新用戶資料
$user_sql = "UPDATE users SET full_name = ?, email = ?, contact_number = ? WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("sssi", $data['full_name'], $data['email'], $data['contact_number'], $user_id);
$user_stmt->execute();

// 更新車輛資料
$vehicle_sql = "UPDATE vehicles SET license_plate = ?, brand = ?, model = ? WHERE owner_id = ?";
$vehicle_stmt = $conn->prepare($vehicle_sql);
$vehicle_stmt->bind_param("sssi", $data['license_plate'], $data['brand'], $data['model'], $user_id);
$vehicle_stmt->execute();

echo json_encode(['success' => true, 'message' => '資料更新成功']);

$user_stmt->close();
$vehicle_stmt->close();
$conn->close();
?>
