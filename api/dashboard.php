<?php
// 啟動 session 用以確認使用者是否登入
session_start();

// 若尚未登入則導向登入頁面
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 資料庫連線設定
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson";
$dbName = "睿煬企業社";

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// 取得目前登入使用者的 ID
$user_id = $_SESSION['user_id'];

// 從 session 中取得使用者姓名，若未設置則預設為「會員名稱」
$userName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "會員名稱";

// 查詢最近的預約資訊：
// 從 appointments 表中選取該用戶的預約，並透過 JOIN 取得對應車輛的車牌號碼
$sqlAppointments = "
    SELECT 
        a.appointment_date, 
        a.appointment_time, 
        a.service_items, 
        v.license_plate 
    FROM appointments a 
    LEFT JOIN vehicles v ON a.vehicle_id = v.vehicle_id 
    WHERE a.customer_id = '$user_id' 
    ORDER BY a.appointment_date DESC 
    LIMIT 2
";
$appointments = [];
$resultAppointments = $conn->query($sqlAppointments);
if ($resultAppointments && $resultAppointments->num_rows > 0) {
    while ($row = $resultAppointments->fetch_assoc()) {
        $appointments[] = $row;
    }
}

// 查詢該使用者的車輛資訊：
// 從 vehicles 表中以 owner_id 過濾出該用戶的車輛資料
$sqlVehicles = "SELECT license_plate, brand, model, year FROM vehicles WHERE owner_id = '$user_id'";
$vehicles = [];
$resultVehicles = $conn->query($sqlVehicles);
if ($resultVehicles && $resultVehicles->num_rows > 0) {
    while ($row = $resultVehicles->fetch_assoc()) {
        $vehicles[] = $row;
    }
}
// 關閉資料庫連線
$conn->close();
?>
