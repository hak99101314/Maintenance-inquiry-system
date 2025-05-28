<?php
header('Content-Type: application/json');

try {
    // 資料庫連接
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 獲取維修單號
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => '缺少維修單號']);
        exit;
    }

    // 查詢維修紀錄基本信息
    $stmt = $pdo->prepare("SELECT * FROM repair_orders WHERE id = ?");
    $stmt->execute([$id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        echo json_encode(['success' => false, 'message' => '找不到維修紀錄']);
        exit;
    }

    // 查詢維修項目
    $stmt = $pdo->prepare("SELECT * FROM repair_items WHERE order_id = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 返回完整資料
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $record['id'],
            'plate_number' => $record['plate_number'],
            'car_model' => $record['car_model'],
            'owner' => $record['owner'],
            'repair_date' => $record['repair_date'],
            'total_cost' => $record['total_cost'],
            'items' => $items
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫錯誤：' . $e->getMessage()]);
}
