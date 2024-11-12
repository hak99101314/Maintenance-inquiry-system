<?php
header('Content-Type: application/json');
include_once '../../config/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'];
$newPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);

$conn = getDbConnection();
$sql = "UPDATE users SET password_hash = ?, reset_token = NULL WHERE reset_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $newPassword, $token);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "密碼重設成功"]);
} else {
    echo json_encode(["success" => false, "message" => "無效的重設請求"]);
}

$stmt->close();
$conn->close();
?>
