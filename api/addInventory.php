<?php
// ========== 檔案說明 ==========
// 檔案名稱：addInventory.php
// 功能說明：提供新增庫存零件的 API 功能
// 使用對象：系統管理員

// 設定回應內容類型為 JSON
header('Content-Type: application/json');

// ========== 請求方法驗證 ==========
// 檢查是否為 POST 請求，若不是則回傳錯誤訊息
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => '無效的請求方法']);
    exit;
}

// ========== 資料庫連線設定 ==========
// 設定資料庫連線參數
$servername = "localhost";    // 資料庫伺服器位址
$username = "root";          // 資料庫使用者名稱
$password = "karry,roy,jackson";              // 資料庫密碼
$dbname = "睿煬企業社";      // 資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查資料庫連線是否成功
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連接失敗']);
    exit;
}

// ========== 請求資料處理 ==========
// 解析 JSON 格式的請求內容
$data = json_decode(file_get_contents('php://input'), true);

// 從請求中取得零件名稱和數量
$partName = $data['partName'];
$quantity = $data['quantity'];

// ========== 資料驗證 ==========
// 檢查必要欄位是否都有提供
if (!isset($partName, $quantity)) {
    echo json_encode(['success' => false, 'message' => '請提供零件名稱和數量']);
    exit;
}

// ========== 資料庫操作 ==========
// 準備 SQL 插入語句，使用參數化查詢防止 SQL 注入
$sql = "INSERT INTO inventory (part_name, quantity) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $partName, $quantity);  // 'si' 表示第一個參數為字串，第二個參數為整數

// 執行插入操作並回傳結果
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '新增失敗']);
}

// ========== 資源釋放 ==========
// 關閉預處理語句和資料庫連線
$stmt->close();
$conn->close();
?>
