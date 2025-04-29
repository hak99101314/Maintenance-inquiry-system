<?php
// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制

// 檢查用戶是否已登入，未登入則重定向到登入頁面
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ========== 使用者資訊處理 ==========
// 取得使用者 ID 和名稱
$user_id = $_SESSION['user_id'];
$userName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "會員名稱";

// ========== 資料庫連線設定 ==========
// 設定資料庫連線參數
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson";
$dbName = "睿煬企業社";

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 使用者資料查詢 ==========
// 查詢使用者上次登入時間
$sqlLastLogin = "SELECT last_login FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sqlLastLogin);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$lastLogin = date("Y-m-d H:i:s");  // 預設為當前時間
if ($row = $result->fetch_assoc()) {
    $lastLogin = date("Y-m-d H:i:s", strtotime($row['last_login']));
}
$stmt->close();

// ========== 預約資訊查詢 ==========
// 初始化預約陣列
$appointments = [];

// 查詢會員的預約資訊
$sqlAppointments = "
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, 
           a.service_items, a.status, v.license_plate 
    FROM appointments a 
    JOIN vehicles v ON a.vehicle_id = v.vehicle_id 
    WHERE a.customer_id = ? 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $conn->prepare($sqlAppointments);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultAppointments = $stmt->get_result();

// 將查詢結果存入陣列
while ($row = $resultAppointments->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();

// ========== 車輛資訊查詢 ==========
// 初始化車輛陣列
$vehicles = [];

// 查詢會員車輛資訊及其維修紀錄數量
$sqlVehicles = "
    SELECT v.vehicle_id, v.license_plate, v.brand, v.model, v.year, 
           IFNULL((SELECT COUNT(*) FROM repair_orders ro WHERE ro.plate_number = v.license_plate), 0) AS repair_count
    FROM vehicles v
    WHERE v.owner_id = ?";
$stmt = $conn->prepare($sqlVehicles);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultVehicles = $stmt->get_result();

// 將查詢結果存入陣列
while ($row = $resultVehicles->fetch_assoc()) {
    // 確保維修次數為整數
    $row['repair_count'] = isset($row['repair_count']) ? (int)$row['repair_count'] : 0;
    $vehicles[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員中心 - 維修查詢系統</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入 FontAwesome 圖示庫 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- 引入 jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* 歡迎區塊樣式 */
        .welcome-section {
            background-color: #f8f9fa;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        /* 儀表板卡片過渡效果 */
        .dashboard-card {
            transition: transform 0.3s ease;
            margin-bottom: 1.5rem;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        /* 狀態徽章樣式 */
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5em 1em;
        }
        body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f5f6fa;
  color: #2c3e50;
  font-size: 17px;
}

h2, h3 {
  font-weight: bold;
  border-bottom: 2px solid #f1c40f;
  padding-bottom: 6px;
  margin-bottom: 20px;
}

.navbar {
  background-color: #2c3e50;
}

.navbar-brand, .nav-link {
  color: #fff !important;
  font-weight: bold;
}

.nav-link:hover {
  color: #f1c40f !important;
}

.dashboard-card {
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 0 15px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
}
.dashboard-card:hover {
  transform: translateY(-5px);
}

.card-title {
  font-size: 20px;
  font-weight: bold;
}

.status-badge {
  font-size: 0.9rem;
  padding: 0.5em 1em;
  border-radius: 12px;
  color: #fff;
}

.table th, .table td {
  vertical-align: middle;
}

footer {
  background-color: #2c3e50;
  color: #fff;
  font-size: 15px;
}

    </style>
</head>

<body>
    <!-- 導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #34495e;">
        <div class="container">
            <a class="navbar-brand" href="index.php">維修查詢系統</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- 左側導覽連結 -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="profile.php">會員中心</a></li>
                    <li class="nav-item"><a class="nav-link" href="appointments.php">預約服務</a></li>
                    <li class="nav-item"><a class="nav-link" href="my-vehicles.php">我的車輛</a></li>
                    <li class="nav-item"><a class="nav-link" href="maintenance-history.php">維修紀錄</a></li>
                    <li class="nav-item"><a class="nav-link" href="report.php">維修報表</a></li>
                </ul>
                <!-- 右側會員下拉選單 -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <!-- 動態輸出使用者名稱 -->
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($userName) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">個人資料</a></li>
                            <li><a class="dropdown-item" href="change-password.php">修改密碼</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <!-- 登出連結，指向本頁加上 action=logout 參數 -->
                            <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php">登出</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 歡迎區塊 -->
    <section class="welcome-section">
        <div class="container">
        <h2>歡迎回來，<?= htmlspecialchars($userName) ?></h2>
        <p>上次登入時間：<?= htmlspecialchars($lastLogin) ?></p>
        </div>
    </section>

    <!-- 主要內容 -->
    <div class="container mb-5">
        <!-- 快速操作卡片 -->
        <div class="row mb-4">
            <!-- 預約維修 -->
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-plus fa-2x mb-3 text-primary"></i>
                        <h5 class="card-title">預約維修</h5>
                        <a href="appointments.php" class="btn btn-primary">立即預約</a>
                    </div>
                </div>
            </div>
            <!-- 新增車輛 -->
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-car fa-2x mb-3 text-success"></i>
                        <h5 class="card-title">新增車輛</h5>
                        <a href="api/add_vehicle.php" class="btn btn-success">新增</a>
                    </div>
                </div>
            </div>
            <!-- 維修紀錄 -->
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-history fa-2x mb-3 text-info"></i>
                        <h5 class="card-title">維修紀錄</h5>
                        <a href="maintenance-history.php" class="btn btn-info text-white">查看</a>
                    </div>
                </div>
            </div>
            <!-- 個人資料 -->
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-user-edit fa-2x mb-3 text-warning"></i>
                        <h5 class="card-title">個人資料</h5>
                        <a href="profile.php" class="btn btn-warning text-white">編輯</a>
                    </div>
                </div>
            </div>
            <!-- 零件估價 -->
<div class="col-md-3">
    <div class="card dashboard-card">
        <div class="card-body text-center">
            <i class="fas fa-money-check-alt fa-2x mb-3 text-danger"></i>
            <h5 class="card-title">估價區</h5>
            <a href="estimate.php" class="btn btn-danger">查看</a>
        </div>
    </div>
</div>

        </div>

 <!-- 最近預約區塊 -->
 <h3 class="mb-4 mt-4">最近預約</h3>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>預約日期</th>
                        <th>預約時間</th>
                        <th>車牌號碼</th>
                        <th>服務項目</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $appt): ?>
                            <tr id="appointment-row-<?= htmlspecialchars($appt['appointment_id']) ?>">
                                <td><?= htmlspecialchars($appt['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($appt['appointment_time']) ?></td>
                                <td><?= htmlspecialchars($appt['license_plate']) ?></td>
                                <td><?= htmlspecialchars($appt['service_items']) ?></td>
                                <td>
                                    <?php
                                    $status = isset($appt['status']) ? $appt['status'] : 'pending';
                                    if ($status === 'pending') {
                                        echo '<span class="badge bg-warning">待確認</span>';
                                    } elseif ($status === 'confirmed') {
                                        echo '<span class="badge bg-success">已確認</span>';
                                    } elseif ($status === 'repair') {
                                        echo '<span class="badge bg-info">維修中</span>';
                                    } elseif ($status === 'completed') {
                                        echo '<span class="badge bg-success">已完成</span>';
                                    } elseif ($status === 'cancelled') {
                                        echo '<span class="badge bg-danger">已取消</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">' . htmlspecialchars($status) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewDetails('<?= htmlspecialchars($appt['appointment_id']) ?>')">詳情</button>
                                    <?php if ($status === 'pending' || $status === 'confirmed'): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="cancelAppointment('<?= htmlspecialchars($appt['appointment_id']) ?>')">取消</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">無預約資訊</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function viewDetails(appointmentId) {
            window.location.href = 'appointment_details.php?id=' + appointmentId;
        }

        function cancelAppointment(appointmentId) {
            if (confirm('確定要取消該預約嗎？')) {
                $.ajax({
                    url: 'api/updateAppointmentStatus.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        appointment_id: appointmentId,
                        status: 'cancelled'
                    }),
                    success: function(response) {
                        if (response.success) {
                            // 更新狀態顯示而不是移除整行
                            const row = $("#appointment-row-" + appointmentId);
                            row.find('td:eq(4)').html('<span class="badge bg-danger">已取消</span>');
                            // 移除取消按鈕
                            row.find('.btn-outline-danger').remove();
                            alert('預約已取消！');
                        } else {
                            alert('更新失敗：' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('系統錯誤，請稍後再試');
                    }
                });
            }
        }
    </script>

 <!-- 我的車輛區塊 -->
 <h3 class="mb-4">我的車輛</h3>
        <div class="row">
            <?php if (count($vehicles) > 0): ?>
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="col-md-4">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($vehicle['license_plate']) ?></h5>
                                <p class="card-text">
                                    <strong>品牌：</strong><?= htmlspecialchars($vehicle['brand']) ?><br>
                                    <strong>型號：</strong><?= htmlspecialchars($vehicle['model']) ?><br>
                                    <strong>年份：</strong><?= htmlspecialchars($vehicle['year']) ?>
                                </p>
                                <?php if ($vehicle['repair_count'] > 0): ?>
                                    <a href="maintenance-history.php?plate=<?= htmlspecialchars($vehicle['license_plate']) ?>" class="btn btn-outline-primary btn-sm">查看維修紀錄</a>
                                <?php else: ?>
                                    <span class="text-muted">無維修紀錄</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">尚無車輛資料，請新增車輛。</p>
                </div>
            <?php endif; ?>
        </div>
    </div>



    <!-- 頁尾 -->
    <footer class="bg-primary text-light py-4 text-center">
        <p>&copy; 2024-2025 維修查詢系統 | 協作單位：睿煬企業社、康寧大學資管科17.林宸皓13.陳彥丞19.陳宗偉</p>
    </footer>

    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
// 關閉資料庫連線
$conn->close();
?>
