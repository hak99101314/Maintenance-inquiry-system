<?php
// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制

// 檢查用戶是否已登入，若未登入則重新導向到登入頁面
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 可根據需求從 Session 取得管理者資訊
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <!-- 確保網頁在各種裝置上都能正確顯示 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增用戶 - 管理員系統</title>
    
    <!-- ========== 外部資源引入 ========== -->
    <!-- 引入 Bootstrap 5 的 CSS 框架 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入後台專用的自定義樣式 -->
    <link rel="stylesheet" href="assets/css/admin_styles.css">
</head>
<body>
    <!-- ========== 導覽列區域 ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 網站標題連結 -->
            <a class="navbar-brand" href="admin_dashboard.php">員工管理系統</a>
            
            <!-- 導覽選單 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- 導覽列選項 -->
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_users.php">用戶管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php" onclick="logout()">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== 主要內容區域 ========== -->
    <div class="container mt-5">
        <!-- 頁面標題 -->
        <h2 class="text-center mb-4">新增用戶</h2>
        
        <!-- 新增用戶表單 -->
        <!-- 表單提交至 api/add_user.php 進行處理 -->
        <form id="add-user-form" class="p-4 border rounded bg-light" method="POST" action="api/add_user.php">
            <!-- 帳號輸入欄位 -->
            <div class="mb-3">
                <label for="username" class="form-label">帳號</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <!-- 姓名輸入欄位 -->
            <div class="mb-3">
                <label for="full_name" class="form-label">姓名</label>
                <input type="text" class="form-control" id="full_name" name="full_name" required>
            </div>
            
            <!-- 電子郵件輸入欄位 -->
            <div class="mb-3">
                <label for="email" class="form-label">電子郵件</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            
            <!-- 角色選擇下拉選單 -->
            <div class="mb-3">
                <label for="role" class="form-label">角色</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="customer">會員</option>
                    <option value="staff">員工</option>
                </select>
            </div>
            
            <!-- 密碼輸入欄位 -->
            <div class="mb-3">
                <label for="password" class="form-label">密碼</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <!-- 提交按鈕 -->
            <button type="submit" class="btn btn-primary w-100">新增用戶</button>
        </form>
    </div>

    <!-- ========== JavaScript 資源引入 ========== -->
    <!-- 引入自定義的 JavaScript 檔案 -->
    <script src="assets/js/admin_users_add.js"></script>
    <!-- 引入 Bootstrap 5 的 JavaScript 框架 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
