<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', 'karry,roy,jackson');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $plate_number = trim($_GET['plate_number'] ?? '');
    if (empty($plate_number)) {
        echo json_encode(['success' => false, 'message' => '車牌號碼為必填']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT 
            u.full_name AS owner,
            COALESCE(u.contact_number, '') AS phone,  
            v.model,
            v.year
        FROM vehicles v
        INNER JOIN users u ON v.owner_id = u.user_id
        WHERE v.license_plate = ?
    ");

    $stmt->execute([$plate_number]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        echo json_encode(['success' => true, 'data' => $vehicle]);
    } else {
        echo json_encode(['success' => false, 'message' => '找不到相關車輛資訊']);
    }
    exit;
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '伺服器錯誤：' . $e->getMessage()]);
    exit;
}
?>
