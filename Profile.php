<?php
// ========== 初始化設定 ==========
session_start();  // 啟動會話機制，用於管理用戶登入狀態

// ========== 登入檢查 ==========
// 檢查用戶是否已登入，若未登入則重新導向到登入頁面
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ========== 資料庫連線設定 ==========
$servername   = "localhost";    // 資料庫伺服器位置
$dbUsername   = "root";         // 資料庫登入帳號
$dbPassword   = "karry,roy,jackson";             // 資料庫登入密碼
$dbName       = "睿煬企業社";    // 資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
// 檢查連線是否成功
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 使用者資訊處理 ==========
// 從會話中獲取當前登入用戶的 ID
$user_id = $_SESSION['user_id'];

// ========== 查詢使用者基本資料 ==========
// 從資料庫中獲取使用者的詳細資訊
$sqlUser = "SELECT username, full_name, email, contact_number FROM users WHERE user_id = '$user_id' LIMIT 1";
$resultUser = $conn->query($sqlUser);
$user = [];  // 初始化使用者資料陣列
if ($resultUser && $resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
}

// ========== 查詢車輛資料 ==========
// 從資料庫中獲取使用者的車輛資訊（目前只取第一筆記錄）
$sqlVehicle = "SELECT license_plate, brand, model FROM vehicles WHERE owner_id = '$user_id' LIMIT 1";
$resultVehicle = $conn->query($sqlVehicle);
$vehicle = [];  // 初始化車輛資料陣列
if ($resultVehicle && $resultVehicle->num_rows > 0) {
    $vehicle = $resultVehicle->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用戶資料 - 維修查詢系統</title>
    <!-- 引入外部資源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- ========== CSS 樣式定義 ========== -->
    <style>
        /* 歡迎區塊樣式設定 */
        .welcome-section {
            background-color: #f8f9fa;  /* 淺灰色背景 */
            padding: 2rem 0;            /* 上下間距 */
            margin-bottom: 2rem;        /* 底部間距 */
        }
        
        /* 儀表板卡片動畫效果 */
        .dashboard-card {
            transition: transform 0.3s ease;  /* 平滑過渡效果 */
            margin-bottom: 1.5rem;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);  /* 滑鼠懸停時上移效果 */
        }
        
        /* 狀態標籤樣式 */
        .status-badge {
            font-size: 0.9rem;          /* 字體大小 */
            padding: 0.5em 1em;         /* 內部間距 */
        }
        body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
    font-size: 17px;
    color: #2c3e50;
}

h2, h4 {
    font-weight: bold;
    color: #2c3e50;
    border-bottom: 2px solid #f1c40f;
    padding-bottom: 5px;
    margin-bottom: 20px;
}

.navbar {
    background-color: #2c3e50;
}

.navbar-brand, .nav-link {
    color: #fff !important;
    font-weight: bold;
}

.nav-link:hover {
    color: #f1c40f !important;
}

form {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.05);
    padding: 30px;
}

.form-label {
    font-weight: bold;
    color: #34495e;
}

.form-control {
    border-radius: 6px;
    font-size: 16px;
}

.btn-primary {
    background-color: #2c3e50;
    border: none;
    font-size: 16px;
    padding: 12px;
}

.btn-primary:hover {
    background-color: #f1c40f;
    color: #2c3e50;
    font-weight: bold;
}

    </style>
</head>

<body>
    <!-- ========== 導覽列 ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 網站標題 -->
            <a class="navbar-brand" href="dashboard.php">維修查詢系統</a>
            
            <!-- 手機版選單按鈕 -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- 導覽列選單 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link active" href="profile.php">用戶資料</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">預約維修</a></li>
                    <li class="nav-item"><a class="nav-link" href="maintenance-history.php">查詢紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="#" onclick="logout()">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== 主要內容區域 ========== -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">基本資料</h2>
        
        <!-- 個人資料表單 -->
        <form id="profile-form" class="p-4 border rounded bg-light" method="post" action="update_profile.php">
            <!-- 個人基本資訊區塊 -->
            <h4 class="mb-3">個人資訊</h4>
            <!-- 帳號欄位（不可修改） -->
            <div class="mb-3">
                <label for="username" class="form-label">帳號</label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?= htmlspecialchars($user['username'] ?? '') ?>" disabled>
            </div>
            <!-- 姓名欄位 -->
            <div class="mb-3">
                <label for="full_name" class="form-label">姓名</label>
                <input type="text" id="full_name" name="full_name" class="form-control" 
                       value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
            </div>
            <!-- 電子郵件欄位 -->
            <div class="mb-3">
                <label for="email" class="form-label">電子郵件</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
            <!-- 聯絡電話欄位 -->
            <div class="mb-3">
                <label for="contact_number" class="form-label">聯絡電話</label>
                <input type="tel" id="contact_number" name="contact_number" class="form-control" 
                       value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" required>
            </div>

            <!-- 車輛資訊區塊 -->
            <h4 class="mt-4 mb-3">車輛資訊</h4>
            <!-- 車牌號碼欄位 -->
            <div class="mb-3">
                <label for="license_plate" class="form-label">車牌號碼</label>
                <input type="text" id="license_plate" name="license_plate" class="form-control" 
                       value="<?= htmlspecialchars($vehicle['license_plate'] ?? '') ?>" required>
            </div>
            <!-- 車輛品牌欄位 -->
            <div class="mb-3">
                <label for="brand" class="form-label">品牌</label>
                <input type="text" id="brand" name="brand" class="form-control" 
                       value="<?= htmlspecialchars($vehicle['brand'] ?? '') ?>">
            </div>
            <!-- 車輛型號欄位 -->
            <div class="mb-3">
                <label for="model" class="form-label">型號</label>
                <input type="text" id="model" name="model" class="form-control" 
                       value="<?= htmlspecialchars($vehicle['model'] ?? '') ?>">
            </div>

            <!-- 提交按鈕 -->
            <button type="submit" class="btn btn-primary w-100" id="save-profile">保存更改</button>
        </form>
    </div>

    <!-- ========== JavaScript 程式碼 ========== -->
    <!-- 引入外部 JavaScript 檔案 -->
    <script src="assets/js/auth.js"></script>
    <script src="assets/js/profile.js"></script>
    <!-- 登出功能實作 -->
    <script>
        // 登出函式：顯示確認對話框並處理登出邏輯
        function logout() {
            if (confirm('確定要登出嗎？')) {
                window.location.href = 'login.php';
            }
        }
    </script>
</body>

</html>
<?php
// 關閉資料庫連線
$conn->close();
?>
