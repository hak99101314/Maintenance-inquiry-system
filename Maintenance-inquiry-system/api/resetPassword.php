<?php
header('Content-Type: application/json');

$pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社', 'root', 'karry,roy,jackson');
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['token']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit;
}

$token = $data['token'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);

// 驗證 Token 是否有效
$stmt = $pdo->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expire_time > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    echo json_encode(['success' => false, 'message' => '重設連結已失效']);
    exit;
}

// 更新用戶密碼
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
$stmt->execute([$password, $reset['user_id']]);

// 刪除 Token
$pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

echo json_encode(['success' => true, 'message' => '密碼已成功重設']);
?>
