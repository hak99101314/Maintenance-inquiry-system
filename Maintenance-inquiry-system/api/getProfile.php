<?php
header('Content-Type: application/json');
session_start();

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

$sql = "SELECT u.username, u.full_name, u.email, u.contact_number, 
               v.license_plate, v.brand, v.model
        FROM users u
        LEFT JOIN vehicles v ON u.user_id = v.owner_id
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => '無法找到用戶資料']);
}

$stmt->close();
$conn->close();
?>
