<?php
header('Content-Type: application/json');
include_once '../../config/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];

$conn = getDbConnection();
$sql = "SELECT user_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    // 假設生成重設密碼的唯一 token
    $token = bin2hex(random_bytes(16));
    $user = $result->fetch_assoc();
    $userId = $user['user_id'];

    // 將 token 儲存在資料庫中（應有一個新欄位如 reset_token）
    $sql = "UPDATE users SET reset_token = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $token, $userId);
    $stmt->execute();

    // 發送包含 token 的重設密碼連結到用戶的郵箱
    $resetLink = "http://yourdomain.com/reset_password.html?token=" . $token;
    // (這裡可以使用 PHPMailer 等庫發送郵件)
    echo json_encode(["success" => true, "message" => "重設密碼連結已發送"]);

} else {
    echo json_encode(["success" => false, "message" => "電子郵件未找到"]);
}

$stmt->close();
$conn->close();
?>
