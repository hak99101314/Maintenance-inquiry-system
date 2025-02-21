<?php
header('Content-Type: application/json');

// 資料庫連接
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

// 查詢系統設定
$sql = "SELECT max_users, backup_schedule FROM system_settings LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $settings = $result->fetch_assoc();
    echo json_encode(['success' => true, 'settings' => $settings]);
} else {
    echo json_encode(['success' => false, 'message' => '無法獲取系統設定']);
}

$conn->close();
?>
