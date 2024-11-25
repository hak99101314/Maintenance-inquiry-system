<?php
header('Content-Type: application/json');

// 接收前端的 JSON 數據
$data = json_decode(file_get_contents("php://input"), true);

// 檢查是否提供帳號和密碼
if (!isset($data['username']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => '請提供帳號和密碼']);
    exit;
}

// 資料庫配置
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

// 建立資料庫連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗：' . $conn->connect_error]);
    exit;
}

// 取得用戶名和密碼
$user = $data['username'];
$pass = $data['password'];

// 檢查用戶
$sql = "SELECT username, full_name, role, password_hash FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();


// 驗證用戶
if ($result->num_rows === 1) {
    $userData = $result->fetch_assoc();

    if (password_verify($pass, $userData['password_hash'])) {
        echo json_encode([
            'success' => true,
            'user_role' => $userData['role'],
            'full_name' => $userData['full_name'],
            'username' => $userData['username']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '帳號或密碼錯誤']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '帳號或密碼錯誤']);
}

// 關閉連接
$stmt->close();
$conn->close();
?>
