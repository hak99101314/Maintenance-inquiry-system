<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => '未登入']);
    exit();
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    echo json_encode(["status" => "error", "message" => "您沒有權限新增維修紀錄"]);
    exit();
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = $_POST;

    if (empty($data['vehicle_id']) || empty($data['repair_date'])) {
        echo json_encode(['status' => 'error', 'message' => '請填寫車輛與維修日期']);
        exit();
    }

    if (empty($data['item']) || !is_array($data['item'])) {
        echo json_encode(['status' => 'error', 'message' => '請至少新增一筆維修項目']);
        exit();
    }

    if (empty($data['total_amount'])) {
        echo json_encode(['status' => 'error', 'message' => '請填寫總金額']);
        exit();
    }

    // 1. 插入維修主表 maintenance_records
    $stmt = $pdo->prepare("
        INSERT INTO maintenance_records 
        (vehicle_id, repair_date, mileage, recommendations, customer_signature, total_cost, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $data['vehicle_id'],
        $data['repair_date'],
        $data['mileage'],
        $data['suggestions'],
        $data['customer_signature'],
        $data['total_amount']
    ]);

    $recordId = $pdo->lastInsertId();

    // 2. 插入維修明細 maintenance_items
    $stmt = $pdo->prepare("
        INSERT INTO maintenance_items 
        (record_id, item_name, specification, quantity, unit_price, subtotal, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($data['item'] as $index => $itemName) {
        $stmt->execute([
            $recordId,
            $itemName,
            $data['spec'][$index] ?? '',
            $data['quantity'][$index] ?? 0,
            $data['price'][$index] ?? 0,
            $data['subtotal'][$index] ?? 0,
            $data['note'][$index] ?? ''
        ]);
    }

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => '資料庫錯誤：' . $e->getMessage()]);
}
?>
