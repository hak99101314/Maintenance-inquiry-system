<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // 資料庫連線設定
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', 'karry,roy,jackson');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 接收資料
    $data = $_POST;

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

    $orderId = $pdo->lastInsertId(); // 取得新插入的維修單 ID

    // 2. 插入維修項目
    $stmt = $pdo->prepare("
        INSERT INTO repair_items (order_id, repair_item, specification, quantity, unit_price, subtotal, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($data['item'] as $index => $item) {
        $stmt->execute([
            $orderId,
            $data['item'][$index],
            $data['spec'][$index],
            $data['quantity'][$index],
            $data['price'][$index],
            $data['subtotal'][$index],
            $data['note'][$index]
        ]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫錯誤：' . $e->getMessage()]);
}
?>
