<?php
// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制
header("Content-Type: application/json"); // 設定回應標頭為 JSON 格式

// 檢查使用者是否已登入且具有管理員權限
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "未授權操作"]);
    exit();
}

// ========== 資料庫連線設定 ==========
// 設定資料庫連線參數
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查資料庫連線是否成功
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "資料庫連線失敗: " . $conn->connect_error]);
    exit();
}

// ========== 請求資料處理 ==========
// 讀取並解析 JSON 格式的請求資料
$data = json_decode(file_get_contents("php://input"), true);

// 檢查是否提供維修單 ID
if (!isset($data['repair_id'])) {
    echo json_encode(["status" => "error", "message" => "缺少維修單 ID"]);
    exit();
}

// 將維修單 ID 轉換為整數，防止 SQL 注入
$repair_id = intval($data['repair_id']);

// ========== 維修單存在性驗證 ==========
// 準備查詢維修單是否存在的 SQL 語句
$check_sql = "SELECT id FROM repair_orders WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $repair_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

// 檢查維修單是否存在
if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "找不到該維修單"]);
    exit();
}
$check_stmt->close();

// ========== 維修單刪除處理 ==========
// 準備刪除維修單的 SQL 語句
$delete_sql = "DELETE FROM repair_orders WHERE id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $repair_id);

// 執行刪除操作並回傳結果
if ($delete_stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "維修單刪除成功"]);
} else {
    echo json_encode(["status" => "error", "message" => "刪除失敗：" . $delete_stmt->error]);
}

// ========== 資源釋放 ==========
// 關閉資料庫連線和預處理語句
$delete_stmt->close();
$conn->close();
?>
