<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $plateNumber = $_GET['plate_number'] ?? '';
    $owner = $_GET['owner'] ?? '';

    $sql = "SELECT id, repair_date, owner, plate_number, car_model, phone, total_cost FROM repair_orders WHERE 1=1";
    $params = [];

    if (!empty($plateNumber)) {
        $sql .= " AND plate_number = ?";
        $params[] = $plateNumber;
    }

    if (!empty($owner)) {
        $sql .= " AND owner LIKE ?";
        $params[] = "%" . $owner . "%";
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
