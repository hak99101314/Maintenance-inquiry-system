<?php
// ========== 初始化設定 ==========
// 管理員維修狀態管理頁面 - 用於管理所有維修訂單的狀態
session_start();  // 啟動會話機制，用於管理使用者登入狀態和資料持久化

// 安全性檢查：確保使用者已登入
// 如果 SESSION 中沒有 user_id，表示使用者尚未登入
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // 重新導向到登入頁面
    exit();  // 終止程式執行，防止未授權訪問
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <!-- 確保網頁在各種裝置上正確顯示 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>維修狀態管理 - 維修查詢系統</title>
    <!-- 引入外部資源 -->
    <!-- Bootstrap CSS - 用於頁面樣式和響應式設計 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 自定義管理員樣式表 - 包含特定的管理介面樣式 -->
    <link rel="stylesheet" href="assets/css/admin_styles.css">
</head>
<body>
    <!-- ========== 導覽列 ========== -->
    <!-- 使用 Bootstrap 的導覽列元件，深色主題 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 網站標題 - 連結到首頁 -->
            <a class="navbar-brand" href="#">員工管理系統</a>
            <!-- 手機版選單按鈕 - 在小螢幕上顯示 -->
            <button class="navbar-toggler" type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" 
                    aria-expanded="false" 
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- 導覽列選單內容 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- 右側對齊的選單項目 -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_orders.php">維修狀態管理</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== 主要內容區域 ========== -->
    <div class="container mt-5">
        <!-- 頁面標題 -->
        <h2 class="text-center mb-4">維修狀態管理</h2>
        <!-- 功能按鈕區域 - 靠右對齊 -->
        <div class="text-end mb-3">
            <!-- 新增按鈕 - 觸發彈出視窗 -->
            <button class="btn btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addOrderModal">新增維修狀態</button>
        </div>
        <!-- 維修狀態資料表格 -->
        <table class="table table-bordered">
            <!-- 表格標題列 - 使用 Bootstrap 的主題色 -->
            <thead class="table-primary">
                <tr>
                    <th>訂單ID</th>
                    <th>用戶名稱</th>
                    <th>維修日期</th>
                    <th>維修內容</th>
                    <th>狀態</th>
                </tr>
            </thead>
            <!-- 表格內容區域 - 由 JavaScript 動態填充 -->
            <tbody id="orderTableBody">
                <!-- 訂單資料將由 JavaScript 動態生成，確保資料即時更新 -->
            </tbody>
        </table>
    </div>

    <!-- ========== 新增訂單彈出視窗 ========== -->
    <!-- Bootstrap Modal 元件 -->
    <div class="modal fade" id="addOrderModal" 
         tabindex="-1" 
         aria-labelledby="addOrderModalLabel" 
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- 彈出視窗標題列 -->
                <div class="modal-header">
                    <h5 class="modal-title" id="addOrderModalLabel">新增訂單</h5>
                    <!-- 關閉按鈕 -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- 彈出視窗主體內容 -->
                <div class="modal-body">
                    <!-- 新增訂單表單 - 使用 JavaScript 處理提交 -->
                    <form id="addOrderForm">
                        <!-- 用戶名稱輸入欄位 - 必填 -->
                        <div class="mb-3">
                            <label for="username" class="form-label">用戶名稱</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   placeholder="輸入用戶名稱" 
                                   required>
                        </div>
                        <!-- 維修日期選擇欄位 - 必填 -->
                        <div class="mb-3">
                            <label for="orderDate" class="form-label">維修日期</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="orderDate" 
                                   required>
                        </div>
                        <!-- 維修內容多行文字輸入 - 必填 -->
                        <div class="mb-3">
                            <label for="orderContent" class="form-label">維修內容</label>
                            <textarea class="form-control" 
                                      id="orderContent" 
                                      rows="3" 
                                      placeholder="輸入訂單內容" 
                                      required></textarea>
                        </div>
                        <!-- 狀態下拉選單 - 必填 -->
                        <div class="mb-3">
                            <label for="status" class="form-label">狀態</label>
                            <select class="form-select" id="status" required>
                                <option value="待處理">待處理</option>
                                <option value="處理中">處理中</option>
                                <option value="已完成">已完成</option>
                            </select>
                        </div>
                        <!-- 表單提交按鈕 -->
                        <button type="submit" class="btn btn-primary w-100">新增維修狀態</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== JavaScript 引入 ========== -->
    <!-- 引入自定義的管理後台 JavaScript - 處理表單提交和資料更新 -->
    <script src="assets/js/admin_orders.js"></script>
    <!-- 引入 Bootstrap JavaScript - 用於互動元件功能 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
