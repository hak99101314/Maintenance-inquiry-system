<?php
header('Content-Type: application/json');
include_once '../../config/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'];
$email = $data['email'];
$password = password_hash($data['password'], PASSWORD_DEFAULT);
$role = 'customer';

$conn = getDbConnection();
$sql = "INSERT INTO users (username, email, password_hash, role, is_enabled) VALUES (?, ?, ?, ?, 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $username, $email, $password, $role);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "註冊失敗，請確認資訊後重試"]);
}
$stmt->close();
$conn->close();
?>
