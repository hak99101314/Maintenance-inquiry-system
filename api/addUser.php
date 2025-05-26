<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

$username = $data['username'];
$full_name = $data['full_name'];
$role = $data['role'];
$password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, full_name, role, password_hash) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $full_name, $role, $password_hash);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
