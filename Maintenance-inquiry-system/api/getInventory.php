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

$sql = "SELECT part_name AS partName, quantity FROM inventory";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $inventory = [];
    while ($row = $result->fetch_assoc()) {
        $inventory[] = $row;
    }
    echo json_encode(['success' => true, 'inventory' => $inventory]);
} else {
    echo json_encode(['success' => false, 'message' => '無庫存數據']);
}

$conn->close();
?>
