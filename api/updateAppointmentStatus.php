<?php
header('Content-Type: application/json');
session_start();

// 顯示錯誤（除錯用）
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

$appointment_id = intval($input['appointment_id']);
$status = $input['status'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// ✅ 修正狀態字串，改為 noshow（不含底線）
$valid_statuses = ['pending', 'confirmed', 'repair', 'completed', 'cancelled', 'noshow'];
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

// 查出該預約的 customer_id
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
if (!in_array($user_role, ['admin', 'staff'])) {
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

// 若是確認狀態，寄送 Email 通知
if ($status === 'confirmed') {
    require_once '../utils/send_email.php';

    $query = $conn->prepare("
        SELECT u.email, u.full_name, a.appointment_date, a.appointment_time, a.service_items
        FROM appointments a
        JOIN users u ON a.customer_id = u.user_id
        WHERE a.appointment_id = ?
    ");
    $query->bind_param("i", $appointment_id);
    $query->execute();
    $res = $query->get_result();
    $info = $res->fetch_assoc();
    $query->close();

    if ($info) {
        $to = $info['email'];
        $name = $info['full_name'];
        $date = $info['appointment_date'];
        $time = $info['appointment_time'];

        $map = [
            'maintenance' => '一般檢修',
            'inspection' => '年度檢查',
            'cleaning' => '車輛清潔'
        ];
        $items = array_map(function($i) use ($map) {
            return $map[trim($i)] ?? $i;
        }, explode(',', $info['service_items']));
        $service = implode('、', $items);

        $subject = "【睿煬企業社】預約已確認";
        $body = "
            <div style='font-family:Arial,sans-serif; background:#f9f9f9; padding:20px; border-radius:8px; max-width:600px; margin:auto; color:#333;'>
                <h2 style='color:#2c3e50;'>親愛的 {$name}，您好：</h2>
                <p>您的預約已成功確認，詳細資訊如下：</p>
                <ul>
                    <li>🛠️ 服務項目：{$service}</li>
                    <li>📅 日期：{$date}</li>
                    <li>⏰ 時間：{$time}</li>
                </ul>
                <p>如有任何疑問，歡迎隨時聯絡我們。</p>
                <p style='margin-top:20px;'>睿煬企業社 敬上</p>
            </div>
        ";

        sendEmail($to, $name, $subject, $body);
    }
}

// 處理未到（noshow）與黑名單邏輯
if ($status === 'noshow') {
    // 累加未到次數
    $stmt = $conn->prepare("UPDATE users SET no_show_count = no_show_count + 1 WHERE user_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->close();

    // 查詢目前未到次數
    $stmt = $conn->prepare("SELECT no_show_count FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    // 超過 3 次未到且不在黑名單
    if ($count >= 3) {
        $stmt = $conn->prepare("SELECT id FROM appointment_blacklist WHERE user_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO appointment_blacklist (user_id, reason) VALUES (?, '預約未到達超過3次')");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
        } else {
            $stmt->close();
        }
    }
}

$conn->close();
echo json_encode(['success' => true, 'message' => '狀態更新成功']);
