<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=睿煬企業社;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    $plateNumber = $data['plateNumber'] ?? '';
    $phoneNumber = $data['phoneNumber'] ?? '';

    if (empty($plateNumber) && empty($phoneNumber)) {
        echo json_encode(['success' => false, 'message' => '請提供車牌號碼或電話']);
        exit;
    }

    $sql = "SELECT owner AS full_name, phone AS phone_number, plate_number, mobile, model, year
            FROM vehicles
            WHERE plate_number = :plate OR phone = :phone
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':plate', $plateNumber);
    $stmt->bindParam(':phone', $phoneNumber);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => '查無資料']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => '資料庫錯誤: ' . $e->getMessage()]);
}
?>
