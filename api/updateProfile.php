<?php
header('Content-Type: application/json');
session_start();

// 資料庫連接
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

// 確保已登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 獲取前端數據
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => '無效的數據']);
    exit;
}

// 更新用戶資料
$user_sql = "UPDATE users SET full_name = ?, email = ?, contact_number = ? WHERE user_id = ?";
$stmt_user = $conn->prepare($user_sql);
$stmt_user->bind_param("sssi", $data['full_name'], $data['email'], $data['contact_number'], $user_id);

if (!$stmt_user->execute()) {
    echo json_encode(['success' => false, 'message' => '更新個人資料失敗']);
    exit;
}

// 更新車輛資料
$vehicle_sql = "UPDATE vehicles SET license_plate = ?, brand = ?, model = ? WHERE owner_id = ?";
$stmt_vehicle = $conn->prepare($vehicle_sql);
$stmt_vehicle->bind_param("sssi", $data['license_plate'], $data['brand'], $data['model'], $user_id);

if (!$stmt_vehicle->execute()) {
    echo json_encode(['success' => false, 'message' => '更新車輛資料失敗']);
    exit;
}

echo json_encode(['success' => true, 'message' => '資料更新成功']);
$stmt_user->close();
$stmt_vehicle->close();
$conn->close();
?>
