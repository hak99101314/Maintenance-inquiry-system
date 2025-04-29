<?php
// ========== 檔案說明 ==========
// 檔案名稱：addMaintenance.php
// 功能說明：提供新增維修紀錄的 API 功能
// 使用對象：系統管理員

// 設定回應內容類型為 JSON
header('Content-Type: application/json');

// ========== 資料庫連線設定 ==========
// 設定資料庫連線參數
$servername = "localhost";    // 資料庫伺服器位址
$username = "root";          // 資料庫使用者名稱
$password = 'karry,roy,jackson';              // 資料庫密碼
$dbname = "睿煬企業社";      // 資料庫名稱

// ========== 請求資料處理 ==========
// 解析 JSON 格式的請求內容
$data = json_decode(file_get_contents('php://input'), true);

// ========== 資料庫連線 ==========
// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查資料庫連線是否成功
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

// ========== 資料驗證與處理 ==========
// 使用空值合併運算子取得傳入的資料，若無則使用預設值
$user_id = $data['user_id'] ?? '';           // 使用者 ID
$vehicle_id = $data['vehicle_id'] ?? '';     // 車輛 ID
$repair_date = $data['repair_date'] ?? '';   // 維修日期
$repair_content = $data['repair_content'] ?? '';  // 維修內容
$repair_cost = $data['repair_cost'] ?? 0;    // 維修費用，預設為 0

// 檢查必要欄位是否都有填寫
if (empty($user_id) || empty($vehicle_id) || empty($repair_date)) {
    echo json_encode(['success' => false, 'message' => '請填寫完整資料']);
    exit;
}

// ========== 資料庫操作 ==========
// 準備 SQL 插入語句，使用參數化查詢防止 SQL 注入
$sql = "INSERT INTO maintenance_records (vehicle_id, date, content, cost, staff_id) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// 綁定參數：i(整數) - vehicle_id, s(字串) - date, s(字串) - content, d(浮點數) - cost, i(整數) - staff_id
$stmt->bind_param("issdi", $vehicle_id, $repair_date, $repair_content, $repair_cost, $user_id);

// 執行插入操作並回傳結果
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '維修紀錄已成功新增']);
} else {
    echo json_encode(['success' => false, 'message' => '新增失敗: ' . $stmt->error]);
}

// ========== 資源釋放 ==========
// 關閉預處理語句和資料庫連線
$stmt->close();
$conn->close();
?>
