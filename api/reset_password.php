<?php
session_start();

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

// 初始化訊息與錯誤變數
$message = "";
$error = "";

// 取得重設密碼用的 token，優先從 POST，再從 GET（第一次載入頁面時）
$token = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = isset($_POST['token']) ? $_POST['token'] : "";
} else {
    $token = isset($_GET['token']) ? $_GET['token'] : "";
}

// 當表單以 POST 方式送出時，執行重設密碼邏輯
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得新密碼（此處僅處理單一密碼欄位，如有需要可加入確認密碼驗證）
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : "";

    if (empty($new_password)) {
        $error = "請輸入新密碼。";
    }

    // 檢查 token 是否存在
    if (empty($token)) {
        $error = "無效的重設連結。";
    }

    if (empty($error)) {
        // 從 password_resets 表查詢對應的 token，取得 user_id 與 expire_time
        $stmt = $conn->prepare("SELECT user_id, expire_time FROM password_resets WHERE token = ? LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $expire_time = strtotime($row['expire_time']);
            // 檢查連結是否已過期（expire_time 大於目前時間才有效）
            if ($expire_time >= time()) {
                $user_id = $row['user_id'];
                // 將新密碼雜湊後更新到 users 表
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_update = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
                $stmt_update->bind_param("si", $new_password_hash, $user_id);
                if ($stmt_update->execute()) {
                    // 更新成功後，刪除此 token 以防重複使用
                    $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                    $stmt_delete->bind_param("s", $token);
                    $stmt_delete->execute();
                    $message = "密碼重設成功！請前往登入。";
                } else {
                    $error = "更新密碼失敗，請稍後再試。";
                }
                $stmt_update->close();
            } else {
                $error = "此重設連結已過期。";
            }
        } else {
            $error = "無效的重設連結。";
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>重設密碼</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 基本全局樣式 */
        body {
            background: linear-gradient(45deg, #1a2980, #26d0ce);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Noto Sans TC', sans-serif;
            margin: 0;
        }
        .container {
            max-width: 500px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <?php if (!empty($message)): ?>
            <!-- 顯示成功訊息，並提供前往登入頁面按鈕 -->
            <div class="alert alert-success text-center">
                <?= htmlspecialchars($message) ?>
            </div>
            <div class="text-center">
                <a href="login.php" class="btn btn-primary">前往登入</a>
            </div>
        <?php else: ?>
            <h2 class="text-center mb-4">重設密碼</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form id="reset-password-form" method="post" class="col-md-12 mx-auto">
                <!-- 隱藏欄位 token，確保使用者必須透過合法連結進入 -->
                <input type="hidden" id="token" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="mb-3">
                    <label for="new_password" class="form-label">新密碼</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">重設密碼</button>
            </form>
        <?php endif; ?>
    </div>
    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
