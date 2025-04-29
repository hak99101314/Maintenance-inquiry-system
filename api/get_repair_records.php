<?php
header('Content-Type: application/json');
session_start();

// 資料庫連線資訊
$host = 'localhost';
$dbname = '睿煬企業社';
$username = 'root';
$password = 'karry,roy,jackson';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 檢查使用者是否已登入
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => '未登入']);
        exit;
    }

    // 驗證 plate_number 參數
    if (!isset($_GET['plate_number']) || empty($_GET['plate_number'])) {
        echo json_encode(['success' => false, 'message' => '缺少車牌號碼']);
        exit;
    }

    $plateNumber = $_GET['plate_number'];

    // 查詢維修紀錄
    $stmt = $pdo->prepare("SELECT repair_date, type, content, cost FROM maintenance_records WHERE plate_number = ? ORDER BY repair_date DESC");
    $stmt->execute([$plateNumber]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $records]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
}
?>
