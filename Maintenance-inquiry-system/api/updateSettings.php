<?php
header('Content-Type: application/json');

// 確保是 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '無效的請求方法']);
    exit;
}

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

// 獲取請求數據
$data = json_decode(file_get_contents('php://input'), true);
$maxUsers = $data['maxUsers'] ?? null;
$backupSchedule = $data['backupSchedule'] ?? null;

if (!$maxUsers || !$backupSchedule) {
    echo json_encode(['success' => false, 'message' => '請提供完整的設定數據']);
    exit;
}

// 更新系統設定
$sql = "UPDATE system_settings SET max_users = ?, backup_schedule = ? WHERE id = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $maxUsers, $backupSchedule);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '系統設定已更新']);
} else {
    echo json_encode(['success' => false, 'message' => '系統設定更新失敗']);
}

$stmt->close();
$conn->close();
?>
