<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['full_name']) || !isset($data['role'])) {
    echo json_encode(['success' => false, 'message' => '請提供完整的用戶數據']);
    exit;
}

// 更新用戶數據
$sql = "UPDATE users SET full_name = ?, role = ? WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $data['full_name'], $data['role'], $data['username']);
$success = $stmt->execute();

if ($success) {
    // 如果有新密碼，進行更新
    if (!empty($data['password'])) {
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $password_sql = "UPDATE users SET password_hash = ? WHERE username = ?";
        $password_stmt = $conn->prepare($password_sql);
        $password_stmt->bind_param("ss", $password_hash, $data['username']);
        $password_stmt->execute();
        $password_stmt->close();
    }
    echo json_encode(['success' => true, 'message' => '用戶更新成功']);
} else {
    echo json_encode(['success' => false, 'message' => '用戶更新失敗']);
}
$stmt->close();
$conn->close();
?>
