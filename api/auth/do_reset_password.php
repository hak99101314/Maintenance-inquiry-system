<?php
// 設定回應格式為 JSON
header('Content-Type: application/json');

// 連接資料庫
$host = 'localhost';
$dbname = '睿煬企業社';
$username = 'root';
$password = 'karry,roy,jackson';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫連線失敗']);
    exit;
}

// 取得前端送來的資料
$input = json_decode(file_get_contents('php://input'), true);
$token = trim($input['token'] ?? '');
$newPassword = trim($input['newPassword'] ?? '');

// 基本檢查
if (empty($token) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => '缺少必要資料']);
    exit;
}

// 查詢 token 是否存在且未過期
$stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = :token");
$stmt->execute(['token' => $token]);
$resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetRequest) {
    echo json_encode(['success' => false, 'message' => '無效或已失效的重設連結']);
    exit;
}

// 檢查 token 是否過期
if (strtotime($resetRequest['expires_at']) < time()) {
    echo json_encode(['success' => false, 'message' => '此連結已過期，請重新申請']);
    exit;
}

// 進行密碼更新
$email = $resetRequest['email'];
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// 更新使用者密碼
$stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
$stmt->execute([
    'password' => $hashedPassword,
    'email' => $email
]);

// 密碼重設成功後，刪除這筆 password_resets 的 token
$stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = :token");
$stmt->execute(['token' => $token]);

// 回傳成功訊息
echo json_encode(['success' => true, 'message' => '密碼重設成功，請重新登入']);
?>
