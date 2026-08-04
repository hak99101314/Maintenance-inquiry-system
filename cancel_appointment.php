<?php
// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制

// 檢查用戶是否已登入
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ========== 預約取消處理 ==========
// 檢查是否有提交的預約取消請求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 獲取並驗證預約ID
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
    
    // 驗證預約ID是否有效
    if ($appointment_id <= 0) {
        die("無效的預約ID");
    }

    // ========== 資料庫連線設定 ==========
    // 設定資料庫連線參數
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "karry,roy,jackson";
    $dbName = "睿煬企業社";

    // 建立資料庫連線
    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

    // 檢查資料庫連線是否成功
    if ($conn->connect_error) {
        die("資料庫連線失敗: " . $conn->connect_error);
    }

    // ========== 預約取消處理 ==========
    // 首先檢查預約是否屬於當前用戶
    $check_sql = "SELECT customer_id FROM appointments WHERE appointment_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $appointment_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $appointment = $result->fetch_assoc();

    // 驗證預約是否屬於當前用戶
    if (!$appointment || $appointment['customer_id'] != $_SESSION['user_id']) {
        die("您沒有權限取消此預約");
    }

    // 準備刪除預約的SQL語句
    $delete_sql = "DELETE FROM appointments WHERE appointment_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $appointment_id);

    // 執行刪除操作並檢查結果
    if ($delete_stmt->execute()) {
        echo "<div class='alert alert-success'>預約已成功取消。</div>";
    } else {
        echo "<div class='alert alert-danger'>取消預約時發生錯誤: " . $delete_stmt->error . "</div>";
    }

    // 關閉資料庫連線和語句
    $check_stmt->close();
    $delete_stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>取消預約</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 頁面基本樣式 */
        body {
            background: #f1f5f9;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        /* 表單容器樣式 */
        .cancel-form {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        /* 標題樣式 */
        .form-title {
            text-align: center;
            margin-bottom: 2rem;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- 導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">預約系統</a>
            <!-- 響應式選單按鈕 -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- 導覽選單 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">預約服務</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">用戶資料</a></li>
                    <li class="nav-item"><a class="nav-link" href="query.php">查詢維修紀錄</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 取消預約表單 -->
    <div class="container">
        <div class="cancel-form">
            <h2 class="form-title">取消預約</h2>
            <form method="post" action="">
                <div class="mb-3">
                    <label for="appointment_id" class="form-label">預約ID</label>
                    <input type="number" class="form-control" id="appointment_id" name="appointment_id" required>
                    <div class="form-text">請輸入要取消的預約ID</div>
                </div>
                <button type="submit" class="btn btn-danger w-100">確認取消預約</button>
            </form>
        </div>
    </div>

    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
