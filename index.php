<?php
// ========== 會話管理與使用者狀態檢查 ==========
session_start();  // 啟動會話管理機制

// 初始化使用者登入狀態和名稱變數
$user_logged_in = false;
$userName = "";

// 檢查使用者是否已登入，並取得使用者名稱
if (isset($_SESSION['user_id'])) {
    $user_logged_in = true;
    // 優先使用 full_name，其次使用 username，最後使用預設值
    $userName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? "會員";
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>維修查詢系統 - 首頁</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/index.js" defer></script>
    <style>
        /* Hero 區塊樣式設定 */
        .hero {
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
            color: #fff;
            text-align: center;
            padding: 100px 20px;
        }
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<!-- 導覽列 -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #34495e;">
    <div class="container">
        <a class="navbar-brand" href="index.php">維修查詢系統</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">首頁</a></li>
                <li class="nav-item"><a class="nav-link" href="appointments.php">預約服務</a></li>
                <?php if ($user_logged_in): ?>
                    <li class="nav-item"><a class="nav-link" href="profile.php">用戶資料</a></li>
                    <li class="nav-item"><a class="nav-link" href="query.php">查詢維修紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">登入</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">註冊</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero 區塊 -->
<section class="hero">
    <div class="container">
        <h1>歡迎使用維修查詢系統</h1>
        <p>提供方便快捷的一站式車輛服務預約體驗，隨時掌握您的維修資訊。</p>
        <a href="appointments.php" class="btn btn-light btn-lg">立即預約</a>
    </div>
</section>

<!-- 核心功能介紹 -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">我們的核心功能</h2>
        <div class="row justify-content-center">
            <!-- 功能卡片：查詢維修紀錄 -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">查詢維修紀錄</h5>
                        <p class="card-text">快速查詢您的車輛維修紀錄，隨時掌握維修進度與歷史。</p>
                        <a href="query.php" class="btn btn-primary">立即查詢</a>
                    </div>
                </div>
            </div>
            <!-- 功能卡片：新增車輛 -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">新增車輛</h5>
                        <p class="card-text">輕鬆新增您的愛車資料，開始使用完整的維修服務。</p>
                        <a href="add_vehicle.php" class="btn btn-primary">立即新增</a>
                    </div>
                </div>
            </div>
            <!-- 功能卡片：預約服務 -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">預約服務</h5>
                        <p class="card-text">選擇合適的時間與服務項目，方便快捷地預約車輛維修服務。</p>
                        <a href="appointments.php" class="btn btn-primary">立即預約</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 聯繫我們 -->
<section class="bg-light py-4">
    <div class="container">
        <h2 class="text-center mb-4">聯繫我們</h2>
        <div class="row justify-content-center">
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <h5 class="mb-4">如有任何問題，歡迎聯繫我們：</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><strong>電子郵件：</strong>support@example.com</li>
                            <li class="mb-2"><strong>電話：</strong>0913-985-808</li>
                            <li><strong>地址：</strong>台東縣關山鎮崁頂路123之1號</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- 地圖 -->
            <div class="col-md-6 mb-3">
                <h4 class="mb-3">地理位置</h4>
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!4v1740296612782!6m8!1m7!1soWGqH7VfmqX1mXXmwmuEXw!2m2!1d22.93674716416446!2d121.1433048074813!3f81.32355164629101!4f-6.819793640543239!5f0.7820865974627469"
                    width="100%" 
                    height="200" 
                    style="border:0; border-radius: 8px;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</section>

<!-- 頁尾 -->
<footer class="bg-primary text-light py-4 text-center">
    <p class="mb-0">&copy; 2024-2025 睿煬企業社維修查詢系統 | 開發人員：林宸皓、陳彥丞、陳宗偉</p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
