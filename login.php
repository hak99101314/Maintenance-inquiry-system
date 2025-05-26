<?php
// ========== 會話管理 ==========
// 開始會話，用於記錄使用者的登入狀態
session_start();

// ========== 登入狀態檢查 ==========
// 如果使用者已經登入，根據其角色重新導向到對應的頁面
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// 初始化錯誤訊息變數
$error = "";

// ========== 登入處理 ==========
// 當收到 POST 請求時（使用者提交登入表單）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 資料庫連線設定
    $servername  = "localhost";    // 資料庫伺服器位置
    $dbUsername  = "root";         // 資料庫登入帳號
    $dbPassword  = "karry,roy,jackson";             // 資料庫登入密碼
    $dbName      = "睿煬企業社";   // 資料庫名稱

    // 建立資料庫連線
    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
    // 檢查連線是否成功
    if ($conn->connect_error) {
        die("資料庫連線失敗: " . $conn->connect_error);
    }

    // 取得並清理使用者輸入的資料
    $username = trim($_POST['username'] ?? "");  // 使用 trim 移除空白字元
    $password = $_POST['password'] ?? "";        // 取得密碼

    // 驗證輸入資料是否完整
    if (empty($username) || empty($password)) {
        $error = "請提供帳號與密碼";
    } else {
        // 準備 SQL 查詢，使用預處理語句防止 SQL 注入
        $stmt = $conn->prepare("SELECT user_id, password_hash, role, full_name FROM users WHERE username = ?");
        if ($stmt) {
            // 綁定參數並執行查詢
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // 檢查是否找到使用者
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // 驗證密碼是否正確
                if (password_verify($password, $user['password_hash'])) {
                    // 記錄登入時間
// ✅ 更新 last_login 欄位為現在時間
  // ✅ 設定時區為台北時間（UTC+8）
  date_default_timezone_set('Asia/Taipei');
  $currentTime = date("Y-m-d H:i:s");

  // ✅ 更新 last_login 時間
  $userId = $user['user_id'];
  $updateSql = "UPDATE users SET last_login = '$currentTime' WHERE user_id = $userId";
  $conn->query($updateSql);

                    // 設定會話變數
                    $_SESSION['user_id']   = $user['user_id'];     // 使用者 ID
                    $_SESSION['full_name'] = $user['full_name'];   // 使用者全名
                    $_SESSION['role']      = $user['role'];        // 使用者角色

                    // 根據使用者角色導向不同的頁面
                    if ($user['role'] === 'admin') {
                        header("Location: admin_dashboard.php");    // 管理員頁面
                        exit();
                    } elseif ($user['role'] === 'staff') {
                        header("Location: staff_dashboard.php");    // 員工頁面
                        exit();
                    } elseif ($user['role'] === 'customer') {
                        header("Location: dashboard.php");         // 客戶頁面
                        exit();
                    } else {
                        header("Location: dashboard.php");         // 預設頁面
                        exit();
                    }
                } else {
                    $error = "密碼錯誤";
                }
            } else {
                $error = "找不到該用戶";
            }
            $stmt->close();
        } else {
            $error = "查詢失敗，請稍後再試";
        }
    }
    $conn->close();  // 關閉資料庫連線
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入 - 維修查詢系統</title>
    <!-- 引入外部資源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- CSS 樣式定義 -->
    <style>
        /* ========== 基本版面設定 ========== */
        body {
            font-family: 'Noto Sans TC', sans-serif;  /* 使用 Google 字體 */
            background: linear-gradient(45deg, #1a2980 0%, #26d0ce 100%);  /* 漸層背景 */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        /* 背景動畫效果 */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/images/pattern.png');
            opacity: 0.1;
            animation: backgroundMove 20s linear infinite;
        }

        /* 背景移動動畫定義 */
        @keyframes backgroundMove {
            from { background-position: 0 0; }
            to { background-position: 100% 100%; }
        }

        /* ========== 登入卡片樣式 ========== */
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* 登入卡片的玻璃擬態效果 */
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        /* 登入卡片懸浮效果 */
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        /* 系統標題樣式 */
        .system-title {
            color: #1a2980;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* ========== 表單元素樣式 ========== */
        .form-floating {
            margin-bottom: 1.2rem;
        }

        /* 輸入框樣式 */
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.3s ease;
        }

        /* 輸入框聚焦效果 */
        .form-control:focus {
            border-color: #1a2980;
            box-shadow: 0 0 0 0.25rem rgba(26, 41, 128, 0.15);
        }

        /* ========== 按鈕樣式 ========== */
        .btn {
            border-radius: 12px;
            font-weight: 500;
            padding: 0.8rem;
            transition: all 0.3s ease;
        }

        /* 登入按鈕樣式 */
        .btn-login {
            background: linear-gradient(45deg, #1a2980 0%, #26d0ce 100%);
            border: none;
            color: white;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        /* 登入按鈕懸浮效果 */
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 41, 128, 0.4);
        }

        /* 註冊按鈕樣式 */
        .btn-register {
            background: linear-gradient(45deg, #26d0ce 0%, #1a2980 100%);
            border: none;
            color: white;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        /* 註冊按鈕懸浮效果 */
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(38, 208, 206, 0.4);
        }

        /* 次要按鈕樣式 */
        .btn-secondary {
            background: #6c757d;
            border: none;
            color: white;
            font-size: 1.1rem;
            letter-spacing: 1px;
        }

        /* 次要按鈕懸浮效果 */
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
            background: #5a6268;
        }

        /* ========== 其他元素樣式 ========== */
        /* 分隔線樣式 */
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
            color: #666;
        }

        /* 分隔線裝飾效果 */
        .divider::before,
        .divider::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 45%;
            height: 1px;
            background: linear-gradient(to right, transparent, #666, transparent);
        }

        .divider::before { left: 0; }
        .divider::after { right: 0; }

        /* 忘記密碼連結樣式 */
        .forgot-password {
            color: #1a2980;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        /* 忘記密碼連結懸浮效果 */
        .forgot-password:hover {
            color: #26d0ce;
            text-decoration: underline;
        }

        /* 開發者資訊樣式 */
        .developer-info {
            color: #666;
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            line-height: 1.6;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <!-- ========== 登入表單區域 ========== -->
    <div class="login-container">
        <div class="login-card animate__animated animate__fadeInUp">
            <!-- 系統標題 -->
            <h2 class="system-title">維修查詢系統</h2>
            
            <!-- 錯誤訊息顯示區 -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <!-- 登入表單 -->
            <form id="loginForm" method="POST" action="login.php">
                <!-- 帳號輸入區 -->
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="帳號" required>
                    <label for="username">帳號</label>
                </div>
                
                <!-- 密碼輸入區 -->
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="密碼" required>
                    <label for="password">密碼</label>
                </div>
                
                <!-- 登入按鈕 -->
                <button type="submit" class="btn btn-login w-100 mb-3">登入系統</button>
                
                <!-- 忘記密碼連結 -->
                <div class="text-center mb-3">
                    <a href="#" class="forgot-password" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                        忘記密碼?
                    </a>
                </div>
            </form>
            
            <!-- 分隔線 -->
            <div class="divider">或</div>
            
            <!-- 註冊按鈕 -->
            <a href="register.php" class="btn btn-register w-100">註冊新帳號</a>
            <div><a href="index.php" class="btn btn-register w-100">返回</a></div>
            <!-- 開發者資訊 -->
            <div class="developer-info">
                協作單位：睿煬企業社<br>
                康寧大學資管科<br>
                17.林宸皓 13.陳彥丞 19.陳宗偉
            </div>
        </div>
    </div>

    <!-- ========== 忘記密碼對話框 ========== -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- 對話框標題 -->
                <div class="modal-header">
                    <h5 class="modal-title">重設密碼</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <!-- 對話框內容 -->
                <div class="modal-body">
                    <form id="forgotPasswordForm" onsubmit="return requestPasswordReset(event)">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="resetEmail" placeholder="電子郵件" required>
                            <label for="resetEmail">電子郵件</label>
                        </div>
                        <button type="submit" class="btn btn-login w-100">發送重設連結</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== JavaScript 引入 ========== -->
    <!-- Bootstrap 核心 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- 自訂登入相關 JavaScript -->
    <script src="assets/js/login.js"></script>
</body>
</html>