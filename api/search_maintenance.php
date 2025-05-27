<?php
header('Content-Type: application/json');

try {
    // 資料庫連接
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 獲取查詢參數
    $plate_number = $_GET['plate_number'] ?? '';
    $repair_date = $_GET['repair_date'] ?? '';

    // 構建查詢條件
    $sql = "SELECT id, plate_number, car_model, owner, repair_date, total_cost FROM repair_orders WHERE 1=1";
    $params = [];

    if (!empty($plate_number)) {
        $sql .= " AND plate_number = ?";
        $params[] = $plate_number;
    }

    if (!empty($repair_date)) {
        $sql .= " AND repair_date = ?";
        $params[] = $repair_date;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($records) {
        echo json_encode(['success' => true, 'data' => $records]);
    } else {
        echo json_encode(['success' => false, 'message' => '查無符合的維修紀錄']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫錯誤：' . $e->getMessage()]);
}
