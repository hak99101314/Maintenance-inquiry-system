<?php
// ========== 初始化設定 ==========
session_start();  // 啟動會話機制，用於管理用戶登入狀態

// ========== 登入檢查 ==========
// 檢查用戶是否已登入，若未登入則重新導向到登入頁面
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ========== 資料庫連線設定 ==========
$servername = "localhost";    // 資料庫伺服器位置
$dbUsername = "root";         // 資料庫登入帳號
$dbPassword = "";             // 資料庫登入密碼
$dbName = "睿煬企業社";       // 資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
// 檢查連線是否成功
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 使用者資訊處理 ==========
// 從會話中獲取當前登入用戶的 ID
$user_id = $_SESSION['user_id'];

// ========== 查詢使用者資料 ==========
// 使用 prepared statement 查詢使用者名稱，防止 SQL 注入攻擊
$sqlUser = "SELECT full_name FROM users WHERE user_id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);  // i 表示整數類型
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userName = "會員名稱";  // 預設名稱
if ($resultUser && $resultUser->num_rows > 0) {
    $row = $resultUser->fetch_assoc();
    $userName = htmlspecialchars($row['full_name']);  // 防止 XSS 攻擊
}
$stmtUser->close();  // 關閉 prepared statement

// ========== 查詢車輛資料 ==========
// 使用 prepared statement 查詢該使用者的所有車輛資料
$sqlVehicles = "SELECT vehicle_id, license_plate, brand, model, year FROM vehicles WHERE owner_id = ?";
$stmtVehicles = $conn->prepare($sqlVehicles);
$stmtVehicles->bind_param("i", $user_id);
$stmtVehicles->execute();
$resultVehicles = $stmtVehicles->get_result();
$vehicles = [];  // 初始化車輛資料陣列
// 將查詢結果存入陣列
if ($resultVehicles && $resultVehicles->num_rows > 0) {
    while ($row = $resultVehicles->fetch_assoc()) {
        $vehicles[] = $row;
    }
}
$stmtVehicles->close();  // 關閉 prepared statement

// 關閉資料庫連線
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的車輛 - 維修查詢系統</title>
    <!-- 引入外部資源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- ========== CSS 樣式定義 ========== -->
    <style>
/* 全站背景與字體設定 */
body {
    background-color: #f5f7fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2c3e50;
}

/* 導覽列設計 */
.navbar {
    background-color: #2c3e50; /* 使用你指定的顏色 */
}
.navbar-brand, .nav-link {
    color: #ecf0f1 !important; /* 白色字 */
    font-weight: 600;
}
.nav-link:hover {
    color: #f1c40f !important; /* Hover時變成亮黃色 */
}

/* 頁面標題 */
h2 {
    font-weight: bold;
    color: #2c3e50;
}

/* 車輛卡片設計 */
.vehicle-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 6px 16px rgba(44, 62, 80, 0.15); /* 淺灰陰影 */
    transition: all 0.3s ease;
    margin-bottom: 2rem;
}
.vehicle-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(44, 62, 80, 0.25);
}
.vehicle-card .card-body {
    padding: 1.5rem;
}

/* 車輛資訊文字 */
.vehicle-card .card-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: #2c3e50;
}
.vehicle-card .card-text {
    font-size: 1rem;
    color: #7f8c8d;
}

/* 新增車輛卡片設計 */
.add-vehicle-card {
    border: 2px dashed #2c3e50;
    border-radius: 16px;
    height: 100%;
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    background-color: #f9fbfc;
}
.add-vehicle-card:hover {
    border-color: #f1c40f;
    background-color: #fef9e7; /* 淡黃色背景 */
    transform: translateY(-5px);
}
.add-vehicle-card i {
    color: #2c3e50;
    font-size: 2.5rem;
}
.add-vehicle-card p {
    margin-top: 0.5rem;
    color: #7f8c8d;
    font-weight: 600;
    font-size: 1.1rem;
}

/* 手機版縮小間距 */
@media (max-width: 768px) {
    .vehicle-card, .add-vehicle-card {
        margin-bottom: 1.5rem;
    }
}
</style>


</head>
<body>
    <!-- ========== 導覽列 ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- 網站標題 -->
            <a class="navbar-brand" href="dashboard.php">維修查詢系統</a>
            
            <!-- 手機版選單按鈕 -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- 導覽列選單 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- 左側選單項目 -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">會員中心</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">預約服務</a></li>
                    <li class="nav-item"><a class="nav-link active" href="my-vehicles.php">我的車輛</a></li>
                </ul>
                <!-- 右側顯示使用者名稱 -->
                <span class="navbar-text text-white">歡迎, <?php echo $userName; ?>!</span>
            </div>
        </div>
    </nav>

    <!-- ========== 主要內容區域 ========== -->
    <div class="container mt-4">
        <h2 class="mb-4">我的車輛</h2>
        <!-- 車輛列表區域 -->
        <div class="row">
            <?php if (empty($vehicles)): ?>
                <!-- 無車輛資料時顯示提示訊息 -->
                <p class="text-muted">您尚未新增任何車輛。</p>
            <?php else: ?>
                <!-- 顯示所有車輛卡片 -->
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="col-md-4">
                        <div class="card vehicle-card">
                            <div class="card-body">
                                <!-- 車輛資訊顯示 -->
                                <h5 class="card-title"><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h5>
                                <p class="card-text">
                                    <strong>車牌號碼：</strong> <?php echo htmlspecialchars($vehicle['license_plate']); ?><br>
                                    <strong>年份：</strong> <?php echo htmlspecialchars($vehicle['year']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- 新增車輛按鈕卡片 -->
            <div class="col-md-4">
                <a href="api/add_vehicle.php" class="card add-vehicle-card d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <i class="fas fa-plus fa-2x text-primary"></i>
                        <p class="mt-2 text-muted">新增車輛</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
