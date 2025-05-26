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
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', 'karry,roy,jackson');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = $_POST;

    // 驗證基本欄位
    if (empty($data['owner']) || empty($data['plate_number']) || empty($data['date'])) {
        echo json_encode(['status' => 'error', 'message' => '請填寫車主、車號與日期']);
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

    // 1. 插入維修單
    $stmt = $pdo->prepare("
        INSERT INTO repair_orders 
        (owner, phone, mobile, plate_number, car_model, year, repair_date, mileage, recommendations, customer_signature, total_cost)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data['owner'], 
        $data['phone'], 
        $data['mobile'], 
        $data['plate_number'], 
        $data['car_model'], 
        $data['year'], 
        $data['date'], 
        $data['mileage'], 
        $data['suggestions'], 
        $data['customer_signature'], 
        $data['total_amount']
    ]);

    $orderId = $pdo->lastInsertId();

    // 2. 插入維修項目
    $stmt = $pdo->prepare("
        INSERT INTO repair_items (order_id, repair_item, specification, quantity, unit_price, subtotal, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($data['item'] as $index => $item) {
        $stmt->execute([
            $orderId,
            $data['item'][$index],
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
