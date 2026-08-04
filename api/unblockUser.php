<?php
// api/unblockUser.php
header('Content-Type: application/json');
session_start();

// 驗證登入與權限
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    echo json_encode(['success' => false, 'message' => '權限不足']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '請使用 POST 請求']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['user_id'])) {
    echo json_encode(['success' => false, 'message' => '缺少 user_id 參數']);
    exit();
}

$user_id = intval($input['user_id']);

$conn = new mysqli("localhost", "root", "karry,roy,jackson", "睿煬企業社");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連線失敗']);
    exit();
}

// 1. 刪除黑名單紀錄
$stmt = $conn->prepare("DELETE FROM appointment_blacklist WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$success = $stmt->execute();
$stmt->close();

// 2. 將 no_show_count 歸零（選擇性，可視你需求）
$conn->query("UPDATE users SET no_show_count = 0 WHERE user_id = $user_id");

$conn->close();

if ($success) {
    echo json_encode(['success' => true, 'message' => '已成功解除黑名單']);
} else {
    echo json_encode(['success' => false, 'message' => '解除失敗']);
}
?>
