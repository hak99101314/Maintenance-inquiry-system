<?php
header('Content-Type: application/json');
session_start();

// 驗證登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '無效的請求方法']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['appointment_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit();
}

$appointment_id = $input['appointment_id'];
$status = $input['status'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

$valid_statuses = ['pending', 'confirmed', 'repair', 'completed', 'cancelled', 'no_show'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => '無效的狀態值']);
    exit();
}

// 資料庫連線
$conn = new mysqli("localhost", "root", "karry,roy,jackson", "睿煬企業社");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連線失敗']);
    exit();
}

// 查出該預約的 user_id
$stmt = $conn->prepare("SELECT customer_id FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => '找不到預約紀錄']);
    exit();
}
$appointment = $result->fetch_assoc();
$customer_id = $appointment['customer_id'];
$stmt->close();

// 權限邏輯
if ($user_role !== 'admin' && $user_role !== 'staff') {
    if ($status !== 'cancelled' || $customer_id !== $user_id) {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        exit();
    }
}

// 更新預約狀態
$stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
$stmt->bind_param("si", $status, $appointment_id);
$success = $stmt->execute();
$stmt->close();

if (!$success) {
    echo json_encode(['success' => false, 'message' => '狀態更新失敗']);
    exit();
}

// 特殊處理：no_show 時更新 no_show_count 與黑名單
if ($status === 'no_show') {
    // 累加 no_show_count
    $conn->query("UPDATE users SET no_show_count = no_show_count + 1 WHERE user_id = $customer_id");

    // 若 >= 3 且尚未進入黑名單
    $check = $conn->query("SELECT no_show_count FROM users WHERE user_id = $customer_id");
    $no_show = $check->fetch_assoc()['no_show_count'];

    if ($no_show >= 3) {
        $black = $conn->query("SELECT * FROM appointment_blacklist WHERE user_id = $customer_id");
        if ($black->num_rows === 0) {
            $reason = "預約未到達 3 次，自動列入黑名單";
            $stmt = $conn->prepare("INSERT INTO appointment_blacklist (user_id, reason) VALUES (?, ?)");
            $stmt->bind_param("is", $customer_id, $reason);
            $stmt->execute();
            $stmt->close();
        }
    }
}

$conn->close();
echo json_encode(['success' => true, 'message' => '狀態更新成功']);
