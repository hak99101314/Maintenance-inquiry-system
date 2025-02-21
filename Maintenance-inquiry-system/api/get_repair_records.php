<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once './config/database.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "請先登入"
    ]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$plate_number = isset($_GET['plate_number']) ? $_GET['plate_number'] : "";

try {
    if (!isset($_GET['plate_number'])) {
        throw new Exception('缺少車牌號碼參數');
    }

    $conn = getDBConnection();
    if (!$conn) {
        throw new Exception('資料庫連接失敗');
    }

    // 測試用：使用固定的 user_id
    $user_id = 1; // 開發時請替換為實際的 $_SESSION['user_id']

    $stmt = $conn->prepare("
        SELECT 
            ro.repair_date,
            ri.repair_item as type,
            ri.specification as content,
            ri.subtotal as cost
        FROM repair_orders ro
        JOIN repair_items ri ON ro.id = ri.order_id
        JOIN vehicles v ON ro.plate_number = v.license_plate
        WHERE ro.plate_number = :plate_number 
        AND v.owner_id = :user_id 
        ORDER BY ro.repair_date DESC
    ");
    
    $stmt->execute([
        'plate_number' => $_GET['plate_number'],
        'user_id' => $user_id
    ]);
    
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'data' => $records,
        'debug' => [
            'plate_number' => $_GET['plate_number'],
            'user_id' => $user_id,
            'count' => count($records)
        ]
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => '系統錯誤，請稍後再試',
        'error' => $e->getMessage()
    ]);
}
?>
