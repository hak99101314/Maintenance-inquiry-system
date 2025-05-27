<?php
// ========== 初始化設定 ==========
// 檔案說明：admin_dashboard.php - 管理員主控台介面
// 功能說明：提供管理員查看系統概況、管理使用者、預約、車輛和維修紀錄的介面

session_start(); // 開始會話，用於管理登入狀態

// 檢查使用者是否已登入且是否具有管理員權限
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}

// ========== 資料庫連線設定 ==========
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson"; // ← 填入你真正的密碼
$dbName     = "睿煬企業社";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
  die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 使用者資訊處理 ==========
$userName = $_SESSION['full_name'] ?? "管理員";

$user_id = $_SESSION['user_id'];
$sqlLastLogin = "SELECT last_login FROM users WHERE user_id = '$user_id' LIMIT 1";
$resultLastLogin = $conn->query($sqlLastLogin);
$lastLogin = "未知";
if ($resultLastLogin && $resultLastLogin->num_rows > 0) {
  $row = $resultLastLogin->fetch_assoc();
  $lastLogin = date("Y-m-d H:i", strtotime($row['last_login']));
}

// ========== 狀態對應設定 ==========
// 預約狀態的中文對應
$status_map = [
  'pending' => '待確認',
  'confirmed' => '已確認',
  'repair' => '維修中',
  'completed' => '維修完成',
  'cancelled' => '已取消',
  'noshow' => '未到'
];

// 維修項目的中文對應
$service_item_map = [
  'maintenance' => '一般檢查',
  'inspection'  => '年度檢查',
  'cleaning'    => '車輛清潔'
];
?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>管理員系統 - 維修查詢系統</title>
  <!-- 引入 Bootstrap CSS 框架，用於響應式設計和基本樣式 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- 引入 FontAwesome 圖示庫，用於顯示各種圖示 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- 引入管理後台專用 CSS 樣式 -->
  <link rel="stylesheet" href="assets/css/admin_styles.css">
  <style>
    /* 歡迎區塊樣式設定 */
    .welcome-section {
      background-color: #f8f9fa;
      /*淺灰色背景*/
      padding: 2rem 0;
      /* 上下內距*/
      margin-bottom: 2rem;
      /*下方外距*/
    }

    /* 儀表板卡片過渡效果設定 */
    .dashboard-card {
      transition: transform 0.3s ease;
      /* 平滑過渡效果*/
      margin-bottom: 1.5rem;
      /*; 下方外距*/
    }

    /* 卡片懸停效果 */
    .dashboard-card:hover {
      transform: translateY(-5px);
      /* 向上移動效果*/
    }

    /* 狀態徽章樣式設定 */
    .status-badge {
      font-size: 0.9rem;
      /*字體大小*/
      padding: 0.5em 1em;
      /* 內距*/
    }

    .dashboard-card {
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
  </style>
</head>
<!-- 引入 jQuery，供 AJAX 使用 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- AJAX 狀態更新函式 -->
<script>
  // 確認預約
  function confirmAppointment(appointmentId) {
    if (confirm('確定要確認這個預約嗎？')) {
      updateStatus(appointmentId, 'confirmed');
    }
  }

  // 開始維修
  function startMaintenance(appointmentId) {
    if (confirm('確定要開始維修嗎？')) {
      updateStatus(appointmentId, 'repair');
    }
  }

  // 完成維修
  function completeMaintenance(appointmentId) {
    if (confirm('確定要完成維修嗎？')) {
      updateStatus(appointmentId, 'completed');
    }
  }

  // 取消預約
  function cancelAppointment(appointmentId) {
    if (confirm('確定要取消這個預約嗎？')) {
      updateStatus(appointmentId, 'cancelled');
    }
  }

  // 共用狀態更新處理
  function updateStatus(id, newStatus) {
    $.ajax({
      url: 'api/updateAppointmentStatus.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({
        appointment_id: id,
        status: newStatus
      }),
      success: function(res) {
        // 如果伺服器回傳的不是 JSON，先嘗試轉換
        try {
          if (typeof res === "string") {
            res = JSON.parse(res);
          }
        } catch (e) {
          alert('伺服器回傳資料格式錯誤');
          return;
        }

        if (res.success) {
          alert('狀態更新成功！');
          location.reload();
        } else {
          alert('更新失敗：' + res.message);
        }
      },
      error: function() {
        alert('發生錯誤，請稍後再試');
      }
    });
  }
</script>

<body>
  <!-- ========== 導覽列 ========== -->
  <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #34495e;">
    <div class="container">
      <!-- 系統標題 -->
      <a class="navbar-brand" href="admin_dashboard.php">管理員系統</a>
      <!-- 響應式選單按鈕 -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- 導覽選單內容 -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <!-- 左側導覽連結 -->
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="admin_users.php">使用者管理</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_appointments.php">預約管理</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_vehicles.php">車輛管理</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_maintenance.php">維修管理</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_parts.php">管理零件</a></li>

        </ul>
        <!-- 右側管理員下拉選單 -->
        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-shield"></i> <?= htmlspecialchars($userName) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="admin_profile.php">管理員資料</a></li>
              <li><a class="dropdown-item" href="admin_settings.php">系統設定</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="logout.php">登出</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- ========== 歡迎區塊 ========== -->
  <section class="welcome-section">
    <div class="container">
      <h2>歡迎回來，<span id="userName"><?= htmlspecialchars($userName) ?></span></h2>
      <p class="text-muted">上次登入時間：<?= $lastLogin ?></p>
    </div>
  </section>

  <!-- ========== 主要內容 ========== -->
  <div class="container mb-5">
    <!-- 快速操作區塊 -->
    <div class="row mb-4">
      <!-- 使用者管理卡片 -->
      <div class="col-md-3">
        <div class="card dashboard-card">
          <div class="card-body text-center">
            <i class="fas fa-users fa-2x mb-3 text-primary"></i>
            <h5 class="card-title">使用者管理</h5>
            <a href="admin_users.php" class="btn btn-primary">管理</a>
          </div>
        </div>
      </div>
      <!-- 預約管理卡片 -->
      <div class="col-md-3">
        <div class="card dashboard-card">
          <div class="card-body text-center">
            <i class="fas fa-calendar-check fa-2x mb-3 text-success"></i>
            <h5 class="card-title">預約管理</h5>
            <a href="admin_appointments.php" class="btn btn-success">管理</a>
          </div>
        </div>
      </div>
      <!-- 維修管理卡片 -->
      <div class="col-md-3">
        <div class="card dashboard-card">
          <div class="card-body text-center">
            <i class="fas fa-tools fa-2x mb-3 text-info"></i>
            <h5 class="card-title">維修管理</h5>
            <a href="admin_maintenance.php" class="btn btn-info text-white">管理</a>
          </div>
        </div>
      </div>
      <!-- 系統設定卡片 -->
      <div class="col-md-3">
        <div class="card dashboard-card">
          <div class="card-body text-center">
            <i class="fas fa-cog fa-2x mb-3 text-warning"></i>
            <h5 class="card-title">系統設定</h5>
            <a href="admin_settings.php" class="btn btn-warning text-white">設定</a>
          </div>
        </div>
      </div>
      <!-- 新增：統計圖表卡片 -->
      <div class="col-md-3">
        <div class="card dashboard-card">
          <div class="card-body text-center">
            <i class="fas fa-chart-pie fa-2x mb-3 text-danger"></i>
            <h5 class="card-title">統計圖表</h5>
            <a href="admin_statistics.php" class="btn btn-danger text-white">查看統計</a>
          </div>
        </div>
      </div>
      <!-- 新增估價單卡片 -->
      <div class="col-md-3">
        <div class="card dashboard-card h-100">
          <div class="card-body text-center">
            <i class="fas fa-tools fa-2x mb-3 text-primary"></i>
            <h5 class="card-title">新增估價單</h5>
            <a href="admin_add_part.php" class="btn btn-primary mt-3">進入</a>
          </div>
        </div>
      </div>
      <!-- 📅 今日排程 -->
      <div class="col-md-3">
        <div class="card dashboard-card h-100">
          <div class="card-body text-center d-flex flex-column justify-content-between">
            <div>
              <i class="fas fa-calendar-alt fa-2x mb-3 text-secondary"></i>
              <h5 class="card-title">本月排程</h5>
            </div>
            <a href="schedule_overview.php" class="btn btn-secondary mt-3">查看排程</a>
          </div>
        </div>
      </div>

    </div>

    <!-- ========== 最近預約列表 ========== -->
    <h3 class="mb-4">最近預約</h3>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>預約日期</th>
            <th>預約時間</th>
            <th>車牌號碼</th>
            <th>維修項目</th>
            <th>狀態</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // 狀態對應樣式
          $status_map = [
            'pending' => '待確認',
            'confirmed' => '已確認',
            'repair' => '維修中',
            'completed' => '已完成',
            'cancelled' => '已取消',
            'noshow' => '未到'
          ];
          $status_class_map = [
            'pending' => 'bg-warning',
            'confirmed' => 'bg-info',
            'repair' => 'bg-primary',
            'completed' => 'bg-success',
            'cancelled' => 'bg-danger',
            'noshow' => 'bg-dark'
          ];
          $service_item_map = [
            'maintenance' => '一般檢修',
            'inspection' => '年度檢查',
            'cleaning' => '車輛清潔'
          ];

          // 查詢預約
          $sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, v.license_plate, a.service_items, a.status
              FROM appointments a
              JOIN vehicles v ON a.vehicle_id = v.vehicle_id
              ORDER BY a.appointment_date DESC, a.appointment_time DESC
              LIMIT 10";
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              echo "<tr>";
              echo "<td>" . htmlspecialchars($row['appointment_date']) . "</td>";
              echo "<td>" . htmlspecialchars($row['appointment_time']) . "</td>";
              echo "<td>" . htmlspecialchars($row['license_plate']) . "</td>";

              // 維修項目中文轉換
              $items = explode(',', $row['service_items']);
              $translated_items = array_map(fn($i) => $service_item_map[trim($i)] ?? trim($i), $items);
              echo "<td>" . htmlspecialchars(implode('、', $translated_items)) . "</td>";

              // 狀態顯示
              $status = $row['status'];
              $badge = $status_class_map[$status] ?? 'bg-secondary';
              $text = $status_map[$status] ?? $status;
              echo "<td><span class='badge $badge'>" . $text . "</span></td>";

              // 操作按鈕
              echo "<td>";
              if ($status == 'pending') {
                echo "<button class='btn btn-sm btn-outline-primary me-1' onclick='updateStatus(" . $row['appointment_id'] . ", \"confirmed\")'>確認</button>";
                echo "<button class='btn btn-sm btn-outline-danger' onclick='updateStatus(" . $row['appointment_id'] . ", \"cancelled\")'>取消</button>";
              } elseif ($status == 'confirmed') {
                echo "<button class='btn btn-sm btn-outline-primary me-1' onclick='startRepair(" . $row['appointment_id'] . ")'>開始維修</button>";
                echo "<button class='btn btn-sm btn-outline-dark' onclick='updateStatus(" . $row['appointment_id'] . ", \"noshow\")'>未到</button>";
              } elseif ($status == 'repair') {
                echo "<button class='btn btn-sm btn-outline-success me-1' onclick='updateStatus(" . $row['appointment_id'] . ", \"completed\")'>完成維修</button>";
                echo "<button class='btn btn-sm btn-outline-dark' onclick='updateStatus(" . $row['appointment_id'] . ", \"noshow\")'>未到</button>";
              }
              echo "</td>";
            }
          } else {
            echo "<tr><td colspan='6' class='text-center'>最近無預約</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>


    <!-- JS 功能：狀態更新與開始維修 -->
    <script>
      function updateStatus(id, status) {
        if (confirm('是否更新狀態為 "' + status + '"？')) {
          fetch('api/updateAppointmentStatus.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                appointment_id: id,
                status: status
              })
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                alert('更新成功');
                location.reload();
              } else {
                alert('錯誤：' + data.message);
              }
            })
            .catch(err => {
              console.error('錯誤：', err);
              alert('更新失敗');
            });
        }
      }

      function startRepair(id) {
        if (confirm('開始維修並建立估價單？')) {
          fetch('api/startRepair.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                appointment_id: id
              })
            })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                window.location.href = `admin_create_estimate.php?estimate_id=${data.estimate_id}&license_plate=${encodeURIComponent(data.license_plate)}`;
              } else {
                alert('錯誤：' + data.message);
              }
            })
            .catch(err => {
              console.error('錯誤：', err);
              alert('系統錯誤');
            });
        }
      }
    </script>

    </script>



    <!-- 系統概況區塊 -->
    <h3 class="mb-4">系統概況</h3>
    <div class="row">
      <!-- 維修統計卡片 -->
      <div class="col-md-4">
        <div class="card dashboard-card">
          <div class="card-body">
            <h5 class="card-title">維修統計</h5>
            <p class="card-text">
              <strong>待處理預約：</strong>8<br>
              <strong>本月完成：</strong>25<br>
            </p>
            <a href="maintenance_statistics.php" class="btn btn-outline-primary btn-sm">詳細資料</a>
          </div>
        </div>
      </div>
      <!-- 系統狀態卡片 -->
      <div class="col-md-4">
        <div class="card dashboard-card">
          <div class="card-body">
            <h5 class="card-title">系統狀態</h5>
            <p class="card-text">
              <strong>系統版本：</strong>1.0.0<br>
              <strong>最後更新：</strong>2025-03-01<br>
              <strong>系統狀態：</strong><span class="text-success">正常</span>
            </p>
            <a href="system_status.php" class="btn btn-outline-primary btn-sm">系統資訊</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 頁尾區塊 -->
  <footer class="bg-primary text-light py-4 text-center">
    <p>&copy; 2024-2025 睿煬企業社維修查詢系統 | 開發人員：林宸皓、陳彥丞、陳宗偉</p>
  </footer>

  <!-- 引入 Bootstrap JS 框架 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // 登出確認函式
    function logout() {
      if (confirm('確定要登出嗎？')) {
        window.location.href = 'logout.php';
      }
    }
  </script>
  <!-- 引入管理後台專用 JavaScript -->
  <script src="assets/js/admin_dashboard.js"></script>
</body>

</html>
<?php
// 關閉資料庫連線，釋放資源
$conn->close();
?>