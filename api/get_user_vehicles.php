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

    $userId = $_SESSION['user_id'];

    // 查詢用戶車輛
    $stmt = $pdo->prepare("SELECT plate_number, brand, car_model FROM vehicles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $vehicles]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
}
?>
