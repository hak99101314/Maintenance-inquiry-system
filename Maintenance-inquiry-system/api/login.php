<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("資料庫連接失敗：" . $conn->connect_error);
}
echo "成功連接至資料庫";


if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => '資料庫連接失敗']));
}

$user = $data['username'];
$pass = $data['password'];

$sql = "SELECT username, role, password_hash FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // 驗證密碼
    if (password_verify($pass, $user['password_hash'])) {
        echo json_encode([
            'success' => true,
            'user_role' => $user['role'],
            'username' => $user['username']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '帳號或密碼錯誤']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '帳號或密碼錯誤']);
}

$stmt->close();
$conn->close();
?>
