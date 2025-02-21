<?php
header('Content-Type: application/json');

// 假設使用者登入後有 session 儲存 user_id
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// 驗證輸入資料
if (
    empty($data['license_plate']) || 
    empty($data['brand']) || 
    empty($data['model']) || 
    empty($data['engine_number']) || 
    empty($data['year']) || 
    empty($data['month'])
) {
    echo json_encode(['success' => false, 'message' => '請填寫完整資料']);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 檢查車牌號碼是否已存在
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vehicles WHERE license_plate = ?");
    $stmt->execute([$data['license_plate']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => '車牌號碼已存在']);
        exit;
    }

    // 插入新車輛資料
    $stmt = $pdo->prepare("
        INSERT INTO vehicles (owner_id, license_plate, brand, model, engine_number, year, month)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $userId,
        $data['license_plate'],
        $data['brand'],
        $data['model'],
        $data['engine_number'],
        $data['year'],
        $data['month']
    ]);

    echo json_encode(['success' => true, 'message' => '車輛已成功新增']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫錯誤：' . $e->getMessage()]);
}
