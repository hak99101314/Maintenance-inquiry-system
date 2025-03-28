<?php
header('Content-Type: application/json');

// 确保请求方法是 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '无效的请求方法']);
    exit;
}

// 数据库连接设置
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

// 获取前端传递的 JSON 数据
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => '用戶ID未提供']);
    exit;
}

// 删除用户
$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '用戶已成功刪除']);
} else {
    echo json_encode(['success' => false, 'message' => '刪除用戶失敗']);
}

$stmt->close();
$conn->close();
