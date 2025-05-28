<?php
// 回傳 JSON 格式
header('Content-Type: application/json');

// 連接資料庫
$host = 'localhost';
$dbname = '睿煬企業社';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫連線失敗']);
    exit;
}

// 引入 PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php'; // 確保你有安裝 phpmailer

// 取得前端送來的 email
$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => '請提供電子郵件']);
    exit;
}

// 確認使用者存在
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => '查無此帳號']);
    exit;
}

// 產生一個 token 和到期時間（例如 1 小時內有效）
$token = bin2hex(random_bytes(16));
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

// 把 token 寫進 password_resets 資料表
$stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires_at)");
$stmt->execute([
    'email' => $email,
    'token' => $token,
    'expires_at' => $expiresAt
]);

// 準備寄送的 reset link
$resetLink = "https://yourdomain.com/reset_password.php?token=$token"; // ⚡ 修改成你的實際網址！

// 寄送 Email
/** @var \PHPMailer\PHPMailer\PHPMailer $mail */
$mail = new PHPMailer(true);

try {
    // SMTP 設定（請改成你的）
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';// SMTP 伺服器
    $mail->SMTPAuth = true;
    $mail->Username = 'c121789581@gmail.com'; //SMTP 帳號
    $mail->Password = 'hwrtwkkamykuvrnd';    // SMTP 密碼
    $mail->SMTPSecure = 'tls';// 加密方式
    $mail->Port = 587;// SMTP 通訊埠

    $mail->setFrom('tf899090@gmail.com', '維修查詢系統');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = '重設您的密碼';
    $mail->Body = "請點擊以下連結來重設您的密碼：<br><a href='$resetLink'>$resetLink</a><br>此連結將在1小時後失效。";

    $mail->send();
    echo json_encode(['success' => true, 'message' => '重設密碼連結已寄送至您的郵箱']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Email 發送失敗：' . $mail->ErrorInfo]);
}
?>
