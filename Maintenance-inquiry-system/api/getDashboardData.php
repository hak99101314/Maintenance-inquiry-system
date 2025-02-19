<?php
// 設定 HTTP 回應內容為 JSON
header('Content-Type: application/json');

// 資料庫連線參數（請根據實際情況修改）
$servername = "localhost";
$username = "root"; // 資料庫使用者名稱
$password = ""; // 資料庫使用者密碼
$dbname = "睿煬企業社";   // 資料庫名稱

// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線狀態
if ($conn->connect_error) {
    die(json_encode(["error" => "資料庫連線失敗: " . $conn->connect_error]));
}

// 從 GET 參數中取得 userId（建議增加驗證機制）
$userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;
if ($userId <= 0) {
    echo json_encode(["error" => "無效的 userId"]);
    exit;
}

// 執行查詢（假設資料表名稱為 'users' 且有欄位 userName）
$stmt = $conn->prepare("SELECT userName FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
    echo json_encode($userData);
} else {
    echo json_encode(["error" => "找不到該使用者"]);
}

$stmt->close();
$conn->close();
?> 