<?php
header('Content-Type: application/json');
session_start();

$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查資料庫連接
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗：' . $conn->connect_error]);
    exit;
}

// 獲取請求的 JSON 資料
$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? null;
$password = $data['password'] ?? null;

// 檢查帳號和密碼是否存在
if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => '請提供帳號和密碼']);
    exit;
}

// 查詢用戶資料
$sql = "SELECT user_id, username, password_hash, full_name, role FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // 驗證密碼
    if (password_verify($password, $user['password_hash'])) {
        // 儲存到 Session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        // 回傳成功訊息和用戶資訊
        echo json_encode([
            'success' => true,
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'user_role' => $user['role']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '密碼錯誤']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '帳號不存在']);
}

$stmt->close();
$conn->close();
