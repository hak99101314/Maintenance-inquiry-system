<?php
// 顯示錯誤訊息（除錯用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 設定回傳 JSON 格式
header('Content-Type: application/json');

// 讀取前端傳入的 JSON 資料
$data = json_decode(file_get_contents("php://input"), true);

// 檢查是否有傳入 appointment_id
if (!isset($data['appointment_id'])) {
  echo json_encode(['success' => false, 'message' => '缺少 appointment_id']);
  exit;
}

$appointment_id = intval($data['appointment_id']);

// 資料庫連線參數
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson";
$dbName = "睿煬企業社";

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
  echo json_encode(['success' => false, 'message' => '資料庫連線失敗：' . $conn->connect_error]);
  exit;
}

// 取得該 appointment 對應的 user_id
$sql_get_user = "SELECT customer_id FROM appointments WHERE appointment_id = ?";
$stmt = $conn->prepare($sql_get_user);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => '找不到該預約資料']);
  $stmt->close();
  $conn->close();
  exit;
}

$row = $result->fetch_assoc();
$user_id = $row['customer_id'];
$stmt->close();

// 從 appointment_blacklist 中刪除該 user_id 的紀錄
$sql_delete = "DELETE FROM appointment_blacklist WHERE user_id = ?";
$stmt = $conn->prepare($sql_delete);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'message' => '已解除黑名單']);
} else {
  echo json_encode(['success' => false, 'message' => '解除失敗：' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
