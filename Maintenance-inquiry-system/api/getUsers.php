<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

$sql = "SELECT user_id, username, full_name, role FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $users]);
} else {
    echo json_encode(['success' => false, 'message' => '沒有用戶資料']);
}

$conn->close();
