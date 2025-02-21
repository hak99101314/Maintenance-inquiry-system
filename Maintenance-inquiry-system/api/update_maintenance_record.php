<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 獲取提交的數據
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['plate_number']) || empty($data['owner']) || empty($data['repair_date']) || empty($data['total_cost'])) {
        echo json_encode(['success' => false, 'message' => '請填寫完整資料']);
        exit;
    }

    // 更新維修紀錄
    $stmt = $pdo->prepare("
        UPDATE repair_orders
        SET plate_number = ?, owner = ?, repair_date = ?, total_cost = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['plate_number'],
        $data['owner'],
        $data['repair_date'],
        $data['total_cost'],
        $data['id']
    ]);

    echo json_encode(['success' => true, 'message' => '維修紀錄已成功更新']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫錯誤：' . $e->getMessage()]);
}
