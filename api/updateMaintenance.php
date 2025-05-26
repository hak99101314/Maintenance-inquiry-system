<?php
// ========== 會話管理與安全驗證 ==========
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// ========== 資料庫連線設定 ==========
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 更新維修紀錄 ==========
$maintenance_id = $_POST['maintenance_id'];
$owner = $_POST['owner'];
$phone = $_POST['phone'];
$mobile = $_POST['mobile'];
$plate_number = $_POST['plate_number'];
$car_model = $_POST['car_model'];
$year = $_POST['year'];
$repair_date = $_POST['repair_date'];
$mileage = $_POST['mileage'];
$recommendations = $_POST['recommendations'];
$customer_signature = $_POST['customer_signature'];
$total_cost = $_POST['total_cost'];

// 更新維修紀錄
$sql = "UPDATE repair_orders SET owner = ?, phone = ?, mobile = ?, plate_number = ?, car_model = ?, year = ?, repair_date = ?, mileage = ?, recommendations = ?, customer_signature = ?, total_cost = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssisssdi", $owner, $phone, $mobile, $plate_number, $car_model, $year, $repair_date, $mileage, $recommendations, $customer_signature, $total_cost, $maintenance_id);
$stmt->execute();

// 關閉資料庫連線
$conn->close();

// 回傳成功訊息
echo json_encode(array('success' => true, 'message' => '更新成功！'));
?>
