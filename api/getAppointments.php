<?php
session_start();
header("Content-Type: application/json");

// 檢查是否已登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "請先登入"]);
    exit();
}

$user_id = $_SESSION['user_id']; // 取得登入的會員 ID

// 資料庫連線
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗: " . $conn->connect_error]);
    exit();
}

// 查詢會員的預約
$sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, 
               a.service_items, a.status, v.license_plate 
        FROM appointments a
        JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        WHERE a.customer_id = ? 
        ORDER BY a.appointment_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode(["success" => true, "data" => $appointments]);

$stmt->close();
$conn->close();
?>
