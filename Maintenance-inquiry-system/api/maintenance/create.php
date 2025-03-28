<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 獲取 POST 數據
    $data = json_decode(file_get_contents("php://input"));

    // 驗證必要欄位
    if (
        empty($data->vehicle_id) ||
        empty($data->date) ||
        empty($data->type) ||
        empty($data->content) ||
        empty($data->items) ||
        empty($data->cost) ||
        empty($data->staff_id)
    ) {
        throw new Exception("所有欄位都是必填的");
    }

    // 準備 SQL 語句
    $query = "INSERT INTO maintenance_records1 
              (vehicle_id, date, type, content, items, cost, staff_id) 
              VALUES 
              (:vehicle_id, :date, :type, :content, :items, :cost, :staff_id)";

    $stmt = $db->prepare($query);

    // 綁定參數
    $stmt->bindParam(":vehicle_id", $data->vehicle_id);
    $stmt->bindParam(":date", $data->date);
    $stmt->bindParam(":type", $data->type);
    $stmt->bindParam(":content", $data->content);
    $stmt->bindParam(":items", $data->items);
    $stmt->bindParam(":cost", $data->cost);
    $stmt->bindParam(":staff_id", $data->staff_id);

    // 執行查詢
    if($stmt->execute()) {
        $record_id = $db->lastInsertId(); // 獲取新增記錄的 ID
        http_response_code(201);
        echo json_encode(array(
            "status" => "success",
            "message" => "維修紀錄新增成功",
            "record_id" => $record_id
        ));
    } else {
        throw new Exception("無法新增維修紀錄");
    }

} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        "status" => "error",
        "message" => $e->getMessage()
    ));
}
?> 