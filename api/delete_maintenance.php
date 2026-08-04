<?php
header('Content-Type: application/json');
session_start();

// 僅限管理員或員工操作
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    echo json_encode(['status' => 'error', 'message' => '無權限操作']);
    exit();
}

// 取得輸入資料
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['repair_id']) || !is_numeric($data['repair_id'])) {
    echo json_encode(['status' => 'error', 'message' => '參數錯誤']);
    exit();
}

$record_id = intval($data['repair_id']);

// 資料庫連線
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => '資料庫連線失敗']);
    exit();
}

// 依序刪除：先刪除明細 → 再刪除主表
$conn->begin_transaction();

try {
    $conn->query("DELETE FROM maintenance_items WHERE record_id = $record_id");
    $conn->query("DELETE FROM maintenance_records WHERE record_id = $record_id");
    $conn->commit();

    echo json_encode(['status' => 'success', 'message' => '已成功刪除維修紀錄']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => '刪除失敗：' . $e->getMessage()]);
}

$conn->close();
