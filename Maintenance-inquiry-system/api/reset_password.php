<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => '資料庫連接失敗']));
}

$token = $data['token'];
$newPassword = password_hash($data['newPassword'], PASSWORD_DEFAULT);

// 驗證 token 並重設密碼
$sql = "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $update_sql = "UPDATE users SET password_hash = ? WHERE user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $newPassword, $user['user_id']);
    if ($update_stmt->execute()) {
        // 刪除已使用的 token
        $delete_sql = "DELETE FROM password_resets WHERE token = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("s", $token);
        $delete_stmt->execute();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => '密碼更新失敗']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '重設連結已失效']);
}

$stmt->close();
$conn->close();
?>
