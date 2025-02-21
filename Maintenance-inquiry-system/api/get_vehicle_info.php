<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // 資料庫連線
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 獲取車牌號碼
    $plate_number = $_GET['plate_number'] ?? '';
    if (empty($plate_number)) {
        echo json_encode(['success' => false, 'message' => '車牌號碼為必填']);
        exit;
    }

    // 聯結查詢
    $stmt = $pdo->prepare("
        SELECT 
            users.full_name AS owner,
            users.contact_number AS contact_number ,
            vehicles.model,
            vehicles.year
        FROM vehicles
        INNER JOIN users ON vehicles.owner_id = users.user_id
        WHERE vehicles.license_plate = ?
    ");
    $stmt->execute([$plate_number]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        echo json_encode(['success' => true, 'data' => $vehicle]);
    } else {
        echo json_encode(['success' => false, 'message' => '找不到相關車輛資訊']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '伺服器錯誤：' . $e->getMessage()]);
}
