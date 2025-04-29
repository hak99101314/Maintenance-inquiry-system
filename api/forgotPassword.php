<?php
header('Content-Type: application/json');

// 資料庫連接
$pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社', 'root', '');

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['email'])) {
    echo json_encode(['success' => false, 'message' => '請輸入電子郵件']);
    exit;
}

$email = $data['email'];

// 檢查用戶是否存在
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => '電子郵件不存在']);
    exit;
}

// 生成重設密碼的 Token
$token = bin2hex(random_bytes(32));
$expire = date('Y-m-d H:i:s', strtotime('+1 hour'));

// 將 Token 存入資料庫
$stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expire_time) VALUES (?, ?, ?)");
$stmt->execute([$user['user_id'], $token, $expire]);

// 發送電子郵件（假設使用 mail 函數）
$resetLink = "http://yourdomain.com/reset_password.php?token=$token";
$subject = "重設您的密碼";
$message = "請點擊以下連結重設您的密碼：$resetLink\n\n連結將在1小時後失效。";
$headers = "From: no-reply@yourdomain.com";

if (mail($email, $subject, $message, $headers)) {
    echo json_encode(['success' => true, 'message' => '重設連結已發送至您的電子郵件']);
} else {
    echo json_encode(['success' => false, 'message' => '發送郵件失敗，請稍後再試']);
}
?>
