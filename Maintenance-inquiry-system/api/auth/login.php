<?php
header('Content-Type: application/json');
include_once '../../config/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'];
$password = $data['password'];

$conn = getDbConnection();
$sql = "SELECT user_id, password_hash, role, username FROM users WHERE username = ? AND is_enabled = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password_hash'])) {
        echo json_encode([
            "success" => true,
            "role" => $user['role'],
            "username" => $user['username']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "密碼錯誤"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "帳號不存在或已停用"]);
}
$stmt->close();
$conn->close();
?>
