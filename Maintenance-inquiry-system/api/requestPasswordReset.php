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

$email = $data['email'];

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    // 此處簡化：可以增加發送重設密碼電子郵件的功能
    echo json_encode(['success' => true, 'message' => '重設連結已發送']);
} else {
    echo json_encode(['success' => false, 'message' => '未找到此電子郵件']);
}

$stmt->close();
$conn->close();
?>
