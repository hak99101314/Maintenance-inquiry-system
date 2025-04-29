<?php
// ========== 初始化設定 ==========
// 引入必要的檔案
require_once 'includes/db_connect.php';    // 引入資料庫連線設定
require_once 'includes/auth_check.php';    // 引入身份驗證檢查

// ========== 權限檢查 ==========
// 確認使用者是否為管理員，如果不是則導向登入頁面
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// ========== 統計資料查詢 ==========
// 1. 查詢待處理的預約數量
$pending_sql = "SELECT COUNT(*) as pending_count FROM appointments WHERE status = 'pending'";
$pending_result = $conn->query($pending_sql);
$pending_count = $pending_result->fetch_assoc()['pending_count'];

// 2. 計算本月完成的維修數量
// 設定本月的起始日期和結束日期
$current_month = date('Y-m');              // 取得當前年月（格式：YYYY-MM）
$start_date = $current_month . "-01";      // 本月第一天
$end_date = date("Y-m-t", strtotime($start_date));  // 本月最後一天

// 查詢本月完成的維修數量
$completed_sql = "SELECT COUNT(*) as completed_count 
                 FROM repair_orders 
                 WHERE repair_date BETWEEN ? AND ?";
$stmt = $conn->prepare($completed_sql);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$completed_result = $stmt->get_result();
$completed_count = $completed_result->fetch_assoc()['completed_count'];

// 3. 計算本月總收入
$revenue_sql = "SELECT COALESCE(SUM(total_cost), 0) as total_revenue 
                FROM repair_orders 
                WHERE repair_date BETWEEN ? AND ?";
$stmt = $conn->prepare($revenue_sql);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$revenue_result = $stmt->get_result();
$total_revenue = $revenue_result->fetch_assoc()['total_revenue'];

// 4. 查詢最常維修的車型
$popular_model_sql = "SELECT car_model, COUNT(*) as repair_count
                     FROM repair_orders
                     WHERE car_model IS NOT NULL AND car_model != ''
                     GROUP BY car_model
                     ORDER BY repair_count DESC
                     LIMIT 1";
$popular_model_result = $conn->query($popular_model_sql);
$popular_model = $popular_model_result->fetch_assoc();

// 除錯用註解：如需檢查數據可取消下方註解
/*
echo "<pre>";
var_dump($start_date, $end_date, $total_revenue, $completed_count, $pending_count);
echo "</pre>";
*/
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>維修統計</title>
    <!-- 引入外部資源 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- ========== 導覽列 ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 網站標題 -->
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-tools me-2"></i>管理員系統
            </a>
            
            <!-- 手機版選單按鈕 -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- 導覽列內容 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- 右側選單 -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link active" href="maintenance_statistics.php">維修統計</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== 主要內容區域 ========== -->
    <div class="container mt-4">
        <!-- 頁面標題 -->
        <h2 class="text-center mb-4">維修統計資訊</h2>
        
        <!-- 統計卡片區域 -->
        <div class="row justify-content-center">
            <!-- 待處理預約卡片 -->
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title">待處理預約</h5>
                        <p class="card-text display-4"><?php echo $pending_count; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- 本月完成維修卡片 -->
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title">本月完成維修</h5>
                        <p class="card-text display-4"><?php echo $completed_count; ?></p>
                    </div>
                </div>
            </div>

            <!-- 本月總收入卡片 -->
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title">本月總收入</h5>
                        <p class="card-text display-4">$<?php echo number_format($total_revenue, 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- 最常維修車型卡片 -->
            <div class="col-md-3">
                <div class="card text-center mb-3">
                    <div class="card-body">
                        <h5 class="card-title">最常維修車型</h5>
                        <p class="card-text display-6"><?php echo $popular_model ? htmlspecialchars($popular_model['car_model']) : '無資料'; ?></p>
                        <p class="text-muted"><?php echo $popular_model ? '維修次數: ' . $popular_model['repair_count'] : ''; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
