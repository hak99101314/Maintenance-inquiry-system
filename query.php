<?php
// ========== 初始化設定 ==========
session_start();  // 啟動會話機制，用於管理用戶登入狀態

// ========== 登出處理 ==========
// 檢查是否有登出請求，如果有則清除會話並導向登入頁面
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = array();        // 清空所有會話變數
    session_destroy();          // 銷毀會話
    header("Location: login.php");
    exit();
}

// ========== 登入檢查 ==========
// 檢查用戶是否已登入，若未登入則重新導向到登入頁面
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ========== 資料庫連線設定 ==========
$servername = "localhost";    // 資料庫伺服器位置
$dbUsername = "root";         // 資料庫登入帳號
$dbPassword = "karry,roy,jackson";             // 資料庫登入密碼
$dbName = "睿煬企業社";       // 資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
// 檢查連線是否成功
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 使用者資訊處理 ==========
// 從會話中獲取當前登入用戶的 ID
$user_id = $_SESSION['user_id'];

// ========== 查詢上次登入時間 ==========
// 從資料庫中獲取用戶的上次登入時間
$sqlLastLogin = "SELECT last_login FROM users WHERE user_id = '$user_id' LIMIT 1";
$resultLastLogin = $conn->query($sqlLastLogin);
$lastLogin = "未知";  // 預設值
if ($resultLastLogin && $resultLastLogin->num_rows > 0) {
    $row = $resultLastLogin->fetch_assoc();
    $lastLogin = date("Y-m-d H:i", strtotime($row['last_login']));  // 格式化日期時間
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查詢維修紀錄 - 維修查詢系統</title>
    <!-- 引入外部資源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- ========== CSS 樣式定義 ========== -->
    <style>
        /* 隱藏元素 */
        .hidden {
            display: none;
        }
        
        /* 按鈕基本樣式 */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;  /* 平滑過渡效果 */
        }
        
        /* 查詢按鈕特殊樣式 */
        .btn-query {
            background: linear-gradient(45deg, #1a2980 0%, #26d0ce 100%);  /* 漸層背景 */
            border: none;
            color: white;
            font-weight: 500;
        }
        .btn-query:hover {
            transform: translateY(-2px);  /* 懸停時上移效果 */
            box-shadow: 0 5px 15px rgba(26, 41, 128, 0.3);  /* 陰影效果 */
            color: white;
        }
        
        /* 外框按鈕樣式 */
        .btn-outline-primary {
            border: 2px solid #1a2980;
            color: #1a2980;
        }
        .btn-outline-primary:hover {
            background: linear-gradient(45deg, #1a2980 0%, #26d0ce 100%);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 41, 128, 0.3);
        }
        
        /* 歡迎區塊樣式 */
        .welcome-section {
            background-color: #f8f9fa;
            padding: 1rem 0;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <!-- ========== 導覽列 ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 網站標題 -->
            <a class="navbar-brand" href="index.php">維修查詢系統</a>
            
            <!-- 手機版選單按鈕 -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- 導覽列選單 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">個人資料</a></li>
                    <li class="nav-item"><a class="nav-link active" href="maintenance-history.php">查詢維修紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="query.php?action=logout">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== 歡迎區塊 ========== -->
    <section class="welcome-section">
        <div class="container">
            <!-- 顯示用戶名稱和上次登入時間 -->
            <p class="mb-0">歡迎回來，<?= htmlspecialchars($_SESSION['full_name'] ?? "會員") ?></p>
            <p class="mb-0 text-muted">上次登入時間：<?= htmlspecialchars($lastLogin) ?></p>
        </div>
    </section>

    <!-- ========== 主要內容區域 ========== -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card animate-fade">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">維修紀錄查詢</h2>
                        
                        <!-- 返回首頁按鈕 -->
                        <div class="text-end mb-3">
                            <a href="dashboard.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>返回首頁
                            </a>
                        </div>

                        <!-- 查詢表單 -->
                        <form id="query-form" class="mb-4">
                            <div class="row align-items-end">
                                <!-- 車輛選擇下拉選單 -->
                                <div class="col-md-9">
                                    <label for="vehicleSelect" class="form-label">選擇車輛</label>
                                    <select class="form-select" id="vehicleSelect" required>
                                        <option value="">請選擇車輛</option>
                                    </select>
                                </div>
                                <!-- 查詢按鈕 -->
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-query w-100">
                                        <i class="fas fa-search me-2"></i>查詢
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- 重新查詢按鈕 -->
                        <div class="text-center mb-3" id="refresh-button" style="display: none;">
                            <button class="btn btn-outline-primary" onclick="refreshData()">
                                <i class="fas fa-sync-alt me-2"></i>重新查詢
                            </button>
                        </div>

                        <!-- 載入中動畫 -->
                        <div id="loading-spinner" class="loading-spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">載入中...</span>
                            </div>
                        </div>

                        <!-- 查詢結果顯示區域 -->
                        <div id="results-container" class="hidden">
                            <h3 class="text-center mb-4">維修紀錄</h3>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>日期</th>
                                            <th>類型</th>
                                            <th>內容</th>
                                            <th>費用 (元)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="results-table-body"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 無資料提示 -->
                        <div id="no-records" class="no-records hidden">
                            <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                            <p>目前沒有維修紀錄</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== JavaScript 程式碼 ========== -->
    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========== 頁面載入初始化 ==========
        // 當 DOM 完全載入後執行
        document.addEventListener('DOMContentLoaded', async function () {
            await loadVehicles();  // 載入車輛清單
        });

        // ========== 車輛資料載入 ==========
        // 從 API 獲取用戶的車輛清單
        async function loadVehicles() {
            try {
                showLoading(true);  // 顯示載入動畫
                const response = await fetch('api/get_user_vehicles.php');
                const result = await response.json();
                const vehicleSelect = document.getElementById('vehicleSelect');
                
                if (result.success) {
                    // 更新下拉選單選項
                    const vehicles = result.data;
                    vehicleSelect.innerHTML = `
                        <option value="">請選擇車輛</option>
                        ${vehicles.map(vehicle => 
                            `<option value="${vehicle.plate_number}">${vehicle.plate_number} - ${vehicle.brand} ${vehicle.car_model}</option>`
                        ).join('')}
                    `;
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('系統錯誤，請稍後再試', 'error');
            } finally {
                showLoading(false);  // 隱藏載入動畫
            }
        }

        // ========== 資料重新整理 ==========
        // 重新載入選定車輛的維修紀錄
        async function refreshData() {
            const vehicleSelect = document.getElementById('vehicleSelect');
            if (vehicleSelect.value) {
                await loadRepairRecords(vehicleSelect.value);
            } else {
                await loadVehicles();
            }
        }

        // ========== 表單提交處理 ==========
        // 處理查詢表單提交事件
        document.getElementById('query-form').addEventListener('submit', async function (e) {
            e.preventDefault();
            const plateNumber = document.getElementById('vehicleSelect').value;
            if (plateNumber) {
                await loadRepairRecords(plateNumber);
                document.getElementById('refresh-button').style.display = 'block';
            } else {
                showToast('請選擇車輛', 'error');
            }
        });

        // ========== 維修紀錄載入 ==========
        // 從 API 獲取指定車輛的維修紀錄
        async function loadRepairRecords(plateNumber) {
            const resultsContainer = document.getElementById('results-container');
            const resultsTableBody = document.getElementById('results-table-body');
            const noRecords = document.getElementById('no-records');
            
            try {
                showLoading(true);  // 顯示載入動畫
                resultsContainer.classList.add('hidden');
                noRecords.classList.add('hidden');

                const response = await fetch(`api/get_repair_records.php?plate_number=${encodeURIComponent(plateNumber)}`);
                const result = await response.json();

                if (result.success) {
                    // 更新表格內容
                    resultsTableBody.innerHTML = result.data.map(record => `
                        <tr class="animate-fade">
                            <td>${formatDate(record.repair_date)}</td>
                            <td>${record.type}</td>
                            <td>${record.content}</td>
                            <td>${formatCurrency(record.cost)}</td>
                        </tr>
                    `).join('');
                    
                    resultsContainer.classList.remove('hidden');
                } else {
                    noRecords.classList.remove('hidden');
                }
            } catch (error) {
                showToast('系統錯誤，請稍後再試', 'error');
            } finally {
                showLoading(false);  // 隱藏載入動畫
            }
        }

        // ========== 工具函數 ==========
        // 顯示/隱藏載入動畫
        function showLoading(show = true) {
            const loadingSpinner = document.getElementById('loading-spinner');
            loadingSpinner.style.display = show ? 'block' : 'none';
        }

        // 格式化日期
        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('zh-TW');
        }

        // 格式化金額
        function formatCurrency(amount) {
            return new Intl.NumberFormat('zh-TW').format(amount);
        }

        // 顯示提示訊息
        function showToast(message, type) {
            // 建立提示訊息容器
            const toastContainer = document.createElement('div');
            toastContainer.style.position = 'fixed';
            toastContainer.style.top = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '1050';
            
            // 建立提示訊息元素
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            // 設定提示訊息內容
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            // 將提示訊息加入頁面
            toastContainer.appendChild(toast);
            document.body.appendChild(toastContainer);
            
            // 顯示提示訊息
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // 提示訊息關閉後移除元素
            toast.addEventListener('hidden.bs.toast', () => {
                toastContainer.remove();
            });
        }
    </script>
</body>

</html>
<?php
// 關閉資料庫連線
$conn->close();
?>
