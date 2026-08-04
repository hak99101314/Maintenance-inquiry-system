<?php
session_start();
header("Content-Type: application/json");

// 確保使用者為員工
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    echo json_encode(["success" => false, "message" => "權限不足"]);
    exit();
}

// 連接資料庫
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "資料庫連線失敗"]);
    exit();
}

// 解析 JSON 請求
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["appointment_id"])) {
    echo json_encode(["success" => false, "message" => "缺少預約 ID"]);
    exit();
}

$appointment_id = intval($data["appointment_id"]);
$start_time = date("Y-m-d H:i:s");

// 更新預約狀態為「進行中」
$sql = "UPDATE appointments SET status = 'repair', repair_start_time = ? WHERE appointment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $start_time, $appointment_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "維修已開始"]);
} else {
    echo json_encode(["success" => false, "message" => "更新失敗"]);
}

$stmt->close();
$conn->close();
?>
