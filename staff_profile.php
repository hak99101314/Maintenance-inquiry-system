<?php
// 啟動 session 並檢查是否登入
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
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

// 取得登入使用者的 ID
$user_id = $_SESSION['user_id'];

// 從 users 表中查詢該使用者的個人資料（帳號、姓名、電子郵件、聯絡電話）
$sqlUser = "SELECT username, full_name, email, contact_number FROM users WHERE user_id = '$user_id' LIMIT 1";
$resultUser = $conn->query($sqlUser);
$user = [];
if ($resultUser && $resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
}

// 關閉資料庫連線
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>員工資料 - 維修查詢系統</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入 Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans TC', sans-serif;
            background: #f8f9fa;
        }
        .navbar {
            background-color: #34495e;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .container {
            margin-top: 2rem;
        }
        .card {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            margin-bottom: 2rem;
        }
        .mb-3, .mb-4, .mt-4 {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- 導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="staff_dashboard.php">維修查詢系統</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="staff_dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link active" href="staff_profile.php">員工資料</a></li>
                    <li class="nav-item"><a class="nav-link" href="staff_users.php">客戶管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="staff_orders.php">維修管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- 主內容：員工基本資料表單 -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">員工基本資料</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label class="form-label">帳號</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">姓名</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">電子郵件</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">聯絡電話</label>
                                <input type="tel" class="form-control" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>" readonly>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 頁尾 -->
    <footer class="bg-primary text-light py-4 text-center">
        <p>&copy; 2024-2025 維修查詢系統 | 協作單位：睿煬企業社、康寧大學資管科17.林宸皓13.陳彥丞19.陳宗偉</p>
    </footer>

    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
