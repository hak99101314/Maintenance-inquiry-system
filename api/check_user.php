<?php
// === 基本資料庫設定 ===
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson";
$dbName = "睿煬企業社";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    echo json_encode(["exists" => false, "message" => "資料庫連線失敗"]);
    exit();
}

$username = $_GET['username'] ?? "";
$email = $_GET['email'] ?? "";

// 檢查帳號
if (!empty($username)) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode(["exists" => $result->num_rows > 0]);
    exit();
}

// 檢查Email
if (!empty($email)) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode(["exists" => $result->num_rows > 0]);
    exit();
}

echo json_encode(["exists" => false]);
$conn->close();
?>
