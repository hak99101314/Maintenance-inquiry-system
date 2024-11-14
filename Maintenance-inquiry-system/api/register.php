<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

// 建立資料庫連接
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => '資料庫連接失敗']));
}

// 檢查帳號和電子郵件是否已存在
$check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("ss", $data['username'], $data['email']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => '帳號或電子郵件已被使用']);
    $stmt->close();
    $conn->close();
    exit;
}

// 將新用戶信息插入資料庫
$hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);  // 建議進行密碼哈希處理
$sql = "INSERT INTO users (username, password_hash, email, full_name, contact_number, role) VALUES (?, ?, ?, ?, ?, 'customer')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $data['username'], $hashed_password, $data['email'], $data['full_name'], $data['contact_number']);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '註冊成功']);
} else {
    echo json_encode(['success' => false, 'message' => '註冊失敗']);
}

// 關閉連接
$stmt->close();
$conn->close();
?>
