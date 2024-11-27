<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

// 獲取 user_id
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => '用戶ID未提供']);
    exit;
}

// 查詢用戶資料
$sql = "SELECT user_id, username, full_name, role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => '用戶不存在']);
}

$stmt->close();
$conn->close();
?>
