<?php
// 設定回應格式為 JSON
header('Content-Type: application/json');
// 開始會話
session_start();

// 檢查使用者是否已登入
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '請先登入']);
    exit();
}

// 檢查是否為 POST 請求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '無效的請求方法']);
    exit();
}

// 獲取並解析 POST 請求中的 JSON 數據
$input = json_decode(file_get_contents('php://input'), true);

// 檢查必要參數是否存在
if (!isset($input['appointment_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit();
}

// 取得請求參數
$appointment_id = $input['appointment_id'];
$status = $input['status'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// 定義有效的狀態列表並驗證
$valid_statuses = ['pending', 'confirmed', 'repair', 'completed', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => '無效的狀態值']);
    exit();
}

// 資料庫連線設定
$servername = "localhost";    // 資料庫伺服器名稱
$dbUsername = "root";         // 資料庫使用者名稱
$dbPassword = "karry,roy,jackson";            // 資料庫密碼
$dbName = "睿煬企業社";      // 資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連線失敗']);
    exit();
}

// 根據使用者角色設定不同的權限
if ($user_role === 'staff' || $user_role === 'admin') {
    // 員工和管理員可以更新所有狀態
    $sql = "UPDATE appointments SET status = ? WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $appointment_id);
} else {
    // 一般會員只能取消自己的預約
    if ($status !== 'cancelled') {
        echo json_encode(['success' => false, 'message' => '權限不足']);
        exit();
    }
    // 確保會員只能取消自己的預約
    $sql = "UPDATE appointments SET status = ? WHERE appointment_id = ? AND customer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status, $appointment_id, $user_id);
}

// 執行更新操作並回傳結果
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '狀態更新成功']);
} else {
    echo json_encode(['success' => false, 'message' => '狀態更新失敗']);
}

// 清理資源並關閉連線
$stmt->close();
$conn->close();
?>
