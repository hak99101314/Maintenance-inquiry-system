<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "睿煬企業社";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sqlUser = "SELECT username, full_name, email, contact_number FROM users WHERE user_id = '$user_id' LIMIT 1";
$resultUser = $conn->query($sqlUser);
$user = [];
if ($resultUser && $resultUser->num_rows > 0) {
    $user = $resultUser->fetch_assoc();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理員資料 - 維修查詢系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans TC', sans-serif;
            background: #f8f9fa;
        }
        .navbar {
            background-color: #2c3e50;
        }
        .navbar-brand {
            font-weight: 700;
        }
        .container {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <!-- 導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">維修查詢系統</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_profile.php">管理員資料</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_users.php">用戶管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_appointments.php">預約管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_orders.php">維修管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主內容 -->
    <div class="container mt-5">
        <h2 class="text-center mb-4 fw-bold border-bottom pb-2">👤 管理員基本資料</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow rounded-4 border-0">
                    <div class="card-header bg-primary text-white fs-5 fw-semibold">
                        個人資訊
                    </div>
                    <div class="card-body p-4">
                        <form>
                            <div class="mb-3">
                                <label class="form-label">帳號</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">姓名</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">電子郵件</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">聯絡電話</label>
                                <input type="tel" class="form-control" value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" readonly>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 頁尾 -->
    <footer class="bg-primary text-light py-4 text-center mt-5">
        <p>&copy; 2024-2025 睿煬企業社維修查詢系統 | 開發人員：林宸皓、陳彥丞、陳宗偉</p>
    </footer>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
