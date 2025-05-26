<?php
// ========== 初始化與驗證 ==========
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// ========== 資料庫連線 ==========
$conn = new mysqli("localhost", "root", "karry,roy,jackson", "睿煬企業社");
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 使用者資料查詢（正規化結構） ==========
$sqlUser = "SELECT username, full_name, email, contact_number FROM users WHERE user_id = ? LIMIT 1";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$user = $resultUser->fetch_assoc();
$stmtUser->close();

// ========== 查詢該用戶的第一台車輛（可擴充為多筆） ==========
$sqlVehicle = "SELECT license_plate, brand, model FROM vehicles WHERE owner_id = ? LIMIT 1";
$stmtVeh = $conn->prepare($sqlVehicle);
$stmtVeh->bind_param("i", $user_id);
$stmtVeh->execute();
$resultVeh = $stmtVeh->get_result();
$vehicle = $resultVeh->fetch_assoc();
$stmtVeh->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>個人資料 - 維修查詢系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; font-size: 17px; }
        .navbar { background-color: #2c3e50; }
        .navbar-brand, .nav-link { color: #fff !important; font-weight: bold; }
        .nav-link:hover { color: #f1c40f !important; }
        h2, h4 { font-weight: bold; color: #2c3e50; border-bottom: 2px solid #f1c40f; padding-bottom: 5px; margin-bottom: 20px; }
        form { background-color: #fff; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.05); padding: 30px; }
        .form-label { font-weight: bold; color: #34495e; }
        .form-control { border-radius: 6px; font-size: 16px; }
        .btn-primary { background-color: #2c3e50; border: none; font-size: 16px; padding: 12px; }
        .btn-primary:hover { background-color: #f1c40f; color: #2c3e50; font-weight: bold; }
    </style>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">維修查詢系統</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
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

<div class="container mt-5">
    <h2 class="text-center mb-4">個人與車輛資料</h2>

    <form id="profile-form" method="post" action="update_profile.php">
        <h4 class="mb-3">個人資訊</h4>
        <div class="mb-3">
            <label for="username" class="form-label">帳號</label>
            <input type="text" id="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
        </div>
        <div class="mb-3">
            <label for="full_name" class="form-label">姓名</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">電子郵件</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="contact_number" class="form-label">聯絡電話</label>
            <input type="tel" name="contact_number" class="form-control" value="<?= htmlspecialchars($user['contact_number']) ?>" required>
        </div>

        <h4 class="mt-4 mb-3">車輛資訊</h4>
        <div class="mb-3">
            <label for="license_plate" class="form-label">車牌號碼</label>
            <input type="text" name="license_plate" class="form-control" value="<?= htmlspecialchars($vehicle['license_plate'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="brand" class="form-label">品牌</label>
            <input type="text" name="brand" class="form-control" value="<?= htmlspecialchars($vehicle['brand'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="model" class="form-label">型號</label>
            <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($vehicle['model'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary w-100">保存更改</button>
    </form>
</div>

<script>
    function logout() {
        if (confirm("確定要登出嗎？")) {
            window.location.href = 'logout.php';
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>