<?php
header('Content-Type: application/json');
session_start();

$data = json_decode(file_get_contents("php://input"), true);

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

$user_id = $_SESSION['user_id'];

// 驗證當前密碼
$sql = "SELECT password_hash FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($data['current_password'], $user['password_hash'])) {
        // 更新新密碼
        $new_password_hash = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_password_hash, $user_id);
        $update_stmt->execute();
        echo json_encode(['success' => true, 'message' => '密碼更新成功']);
    } else {
        echo json_encode(['success' => false, 'message' => '當前密碼錯誤']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '無法找到用戶資料']);
}

$stmt->close();
$conn->close();
?>
