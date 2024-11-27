<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查資料庫連接
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => '資料庫連接失敗：' . $conn->connect_error]));
}

// 插入用戶資訊
$conn->begin_transaction();
try {
    // 插入用戶資料
    $user_sql = "INSERT INTO users (username, email, password_hash, full_name, contact_number, role) VALUES (?, ?, ?, ?, ?, 'customer')";
    $stmt_user = $conn->prepare($user_sql);
    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt_user->bind_param("sssss", $data['username'], $data['email'], $password_hash, $data['full_name'], $data['contact_number']);
    $stmt_user->execute();

    $user_id = $conn->insert_id; // 獲取新插入的用戶 ID

    // 插入車輛資料
    $vehicle_sql = "INSERT INTO vehicles (license_plate, brand, engine_number, year, month, owner_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_vehicle = $conn->prepare($vehicle_sql);
    $stmt_vehicle->bind_param(
        "sssiii",
        $data['plate_number'],
        $data['car_make'],
        $data['engine_number'],
        $data['year_of_manufacture'],
        $data['month_of_manufacture'],
        $user_id
    );
    $stmt_vehicle->execute();

    $conn->commit(); // 提交交易
    echo json_encode(['success' => true, 'message' => '註冊成功！']);
} catch (Exception $e) {
    $conn->rollback(); // 回滾交易
    echo json_encode(['success' => false, 'message' => '註冊失敗：' . $e->getMessage()]);
} finally {
    $stmt_user->close();
    $stmt_vehicle->close();
    $conn->close();
}
