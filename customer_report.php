<?php
// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制

// 檢查用戶是否已登入，未登入則重定向到登入頁面
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>顧客維修統計報表</title>
    <!-- 引入 Bootstrap CSS 框架 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入自定義樣式表 -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- 引入 Chart.js 圖表庫 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* 頁面基本樣式設定 */
        body {
            background: #f1f5f9;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        /* 報表卡片容器樣式 */
        .report-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 4rem;
            margin-bottom: 4rem;
        }
        /* 報表標題樣式 */
        .report-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        /* 表單標籤樣式 */
        .form-label {
            font-weight: bold;
        }
        /* 圖表容器樣式 */
        .chart-container {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <!-- 導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 網站標題 -->
            <a class="navbar-brand" href="index.php">顧客維修統計報表</a>
            <!-- 響應式選單按鈕 -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- 導覽選單 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">預約服務</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">用戶資料</a></li>
                    <li class="nav-item"><a class="nav-link" href="query.php">查詢維修紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 報表主要內容區塊 -->
    <div class="container">
        <div class="report-card mx-auto" style="max-width: 800px;">
            <h2 class="report-title">顧客維修統計報表</h2>
            <!-- 使用者資訊顯示區域 -->
            <h4 class="text-center" id="user-info"></h4>
            <!-- 統計資料表格 -->
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>姓名</th>
                        <th>總維修金額</th>
                        <th>累計來店次數</th>
                    </tr>
                </thead>
                <tbody id="report-body">
                    <!-- 動態填充報表數據 -->
                </tbody>
            </table>
            <!-- 圖表顯示區域 -->
            <div class="chart-container">
                <canvas id="repairChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 前端 JavaScript：處理報表資料獲取與顯示 -->
    <script>
        // 從 API 獲取報表資料
        fetch('/api/get_customer_report.php')
        .then(response => response.json())
        .then(data => {
            // 獲取 DOM 元素
            const reportBody = document.getElementById('report-body');
            const userInfo = document.getElementById('user-info');
            const serviceData = {};

            // 顯示登入用戶資訊
            if (data.length > 0) {
                userInfo.innerText = `登入用戶：${data[0].name}`;
            }

            // 遍歷資料並填充表格
            data.forEach(record => {
                // 添加表格行
                reportBody.innerHTML += `
                    <tr>
                        <td>${record.name}</td>
                        <td>${record.total_amount}</td>
                        <td>${record.visit_count}</td>
                    </tr>
                `;
                
                // 統計各服務項目的總金額
                if (serviceData[record.service]) {
                    serviceData[record.service] += record.total_amount;
                } else {
                    serviceData[record.service] = record.total_amount;
                }
            });

            // 使用 Chart.js 繪製圓餅圖
            const ctx = document.getElementById('repairChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',  // 設定圖表類型為圓餅圖
                data: {
                    labels: Object.keys(serviceData),  // 服務項目名稱
                    datasets: [{
                        data: Object.values(serviceData),  // 各服務項目金額
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],  // 圖表顏色
                    }]
                },
                options: {
                    responsive: true,  // 響應式設計
                    plugins: {
                        legend: {
                            position: 'top',  // 圖例位置
                        }
                    }
                }
            });
        })
        .catch(error => console.error('報表加載失敗:', error));  // 錯誤處理
    </script>
    
    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
