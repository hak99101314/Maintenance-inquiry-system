<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once './config/database.php';

// 假設使用者登入後有 session 儲存 user_id
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

try {
    $query = "SELECT id, plate_number, car_model, brand
              FROM vehicles 
              WHERE user_id = ?
              ORDER BY created_at DESC";
              
    $stmt = $db->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($vehicles) {
        echo json_encode([
            "success" => true,
            "data" => $vehicles
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "尚未登記任何車輛"
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "資料庫錯誤"
    ]);
}
