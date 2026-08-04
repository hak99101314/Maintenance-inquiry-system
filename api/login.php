<?php
session_start();

// 若已登入則直接導向會員中心
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = ""; // 用來儲存錯誤訊息

// 當表單以 POST 方式送出時執行登入驗證
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    // 取得使用者提交的帳號與密碼，並做基本防注入處理
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // 從 users 表中查詢對應的使用者資料
    $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // 使用 password_verify 驗證密碼
        if (password_verify($password, $user['password_hash'])) {
            // 驗證成功，設定 session 並導向 dashboard
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "帳號或密碼錯誤";
        }
    } else {
        $error = "帳號或密碼錯誤";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入 - 維修查詢系統</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入 Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- 與 dashboard.php 相同的 CSS 樣式 -->
    <style>
        body {
            font-family: 'Noto Sans TC', sans-serif;
            background: linear-gradient(45deg, #1a2980, #26d0ce);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
        }
        .system-title {
            color: #1a2980;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #ccc;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #1a2980;
            box-shadow: 0 0 0 0.2rem rgba(26, 41, 128, 0.25);
        }
        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 0.65rem 1rem;
        }
        .btn-primary {
            background-color: #1a2980;
            border-color: #1a2980;
        }
        .btn-primary:hover {
            background-color: #15306b;
            border-color: #15306b;
        }
        .alert {
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h2 class="system-title">維修查詢系統</h2>
        <!-- 錯誤訊息顯示區 -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="login.php">
            <div class="mb-3">
                <label for="username" class="form-label">帳號</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="請輸入帳號" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">密碼</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="請輸入密碼" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">登入系統</button>
            <div class="mt-3 text-center">
                <a href="register.php" style="color:#1a2980;">註冊新帳號</a>
            </div>
            <div class="mt-2 text-center">
                <a href="index.php" class="btn btn-secondary w-100">返回首頁</a>
            </div>
        </form>
    </div>
    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
