<?php
// get_customer_details.php
// 此檔案用於根據傳入的 user_id 參數查詢並回傳客戶詳細資料（包含帳號、姓名、電子郵件、聯絡電話）

// 啟用 session（若需要檢查使用者權限，可進行驗證）
session_start();

// 若使用者未登入，回傳未授權訊息（此處可根據需求調整）
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未授權存取']);
    exit();
}

// 資料庫連線設定
$servername = "localhost";               // 資料庫伺服器位置
$dbUsername = "root";                    // 資料庫使用者名稱
$dbPassword = "";        // 資料庫密碼
$dbName = "睿煬企業社";                   // 資料庫名稱

// 建立 MySQLi 連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// 檢查連線是否成功
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '資料庫連線失敗: ' . $conn->connect_error]);
    exit();
}

// 檢查 GET 參數中是否有 user_id
if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => '缺少必要參數']);
    exit();
}
$user_id = intval($_GET['user_id']); // 將 user_id 轉為整數

// 使用預處理語句查詢該客戶的詳細資料
$stmt = $conn->prepare("SELECT user_id, username, full_name, email, contact_number FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// 若查詢有資料，則回傳詳細資訊
if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => '查無資料']);
}

// 關閉預處理語句及資料庫連線
$stmt->close();
$conn->close();
?>
