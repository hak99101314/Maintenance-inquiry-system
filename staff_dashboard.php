<?php
// ========== 基本設定與安全檢查 ==========
// 開始會話，用於管理使用者登入狀態
session_start();

// 檢查使用者是否已登入且是否為員工身份
// 如果未登入或不是員工，會被重新導向到登入頁面
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// ========== 資料庫連線設定 ==========
$servername = "localhost";    // 資料庫伺服器位置
$dbUsername = "root";         // 資料庫登入帳號
$dbPassword = "";            // 資料庫登入密碼
$dbName = "睿煬企業社";      // 資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
// 檢查連線是否成功
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 使用者資訊處理 ==========
// 從 session 中取得員工名稱，如果沒有則顯示「員工」
$userName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "員工";

// 查詢員工的上次登入時間
$user_id = $_SESSION['user_id'];
$sqlLastLogin = "SELECT last_login FROM users WHERE user_id = '$user_id' LIMIT 1";
$resultLastLogin = $conn->query($sqlLastLogin);
$lastLogin = "未知";
if ($resultLastLogin && $resultLastLogin->num_rows > 0) {
    $row = $resultLastLogin->fetch_assoc();
    $lastLogin = date("Y-m-d H:i", strtotime($row['last_login']));
}

// ========== 狀態對應設定 ==========
// 定義預約狀態的中文對應
$status_map = [
    'pending' => '待確認',
    'confirmed' => '已確認',
    'repair' => '維修中',
    'completed' => '維修完成',
    'cancelled' => '已取消',
    'noshow' => '未到'

];
// 維修項目對應中文
$service_item_map = [
  'maintenance' => '一般檢查',
  'inspection' => '年度檢查',
  'cleaning' => '車輛清潔'
  
];

?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>員工系統 - 維修查詢系統</title>
  <!-- 引入外部資源 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- CSS 樣式定義 -->
  <style>
    /* 歡迎區塊的背景和間距設定 */
    .welcome-section {
      background-color: #f8f9fa;
      padding: 2rem 0;
      margin-bottom: 2rem;
    }
    
    /* 儀表板卡片的動畫效果 */
    .dashboard-card {
      transition: transform 0.3s ease;
      margin-bottom: 1.5rem;
    }
    .dashboard-card:hover {
      transform: translateY(-5px); /* 滑鼠移過時向上浮動 */
    }
    
    /* 狀態標籤的樣式 */
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
  <!-- ========== 導覽列 ========== -->
  <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #34495e;">
    <div class="container">
      <!-- 網站標題 -->
      <a class="navbar-brand" href="staff_dashboard.php">員工系統</a>
      
      <!-- 手機版選單按鈕 -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <!-- 左側功能選單 -->
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="handle_appointments.php">處理預約</a></li>
          <li class="nav-item"><a class="nav-link" href="maintenance_records.php">維修紀錄</a></li>
          <li class="nav-item"><a class="nav-link" href="staff_users.php">客戶管理</a></li>
          
        </ul>
        
        <!-- 右側使用者選單 -->
        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-tie"></i> <?= htmlspecialchars($userName) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="staff_profile.php">個人資料</a></li>
              <li><hr class="dropdown-divider"></li>
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
    <h2>歡迎回來，<span style="color:#f1c40f"><?= htmlspecialchars($userName) ?></span></h2>

      <p class="text-muted">上次登入時間：<?= $lastLogin ?></p>
    </div>
  </section>

  <!-- ========== 主要內容區塊 ========== -->
  <div class="container mb-5">
    <!-- 快速功能按鈕區 -->
<div class="row mb-4">
  <!-- 處理預約 -->
  <div class="col-md-3">
    <div class="card dashboard-card h-100">
      <div class="card-body text-center d-flex flex-column justify-content-between">
        <div>
          <i class="fas fa-calendar-check fa-2x mb-3 text-primary"></i>
          <h5 class="card-title">處理預約</h5>
        </div>
        <a href="handle_appointments.php" class="btn btn-primary mt-3">查看預約</a>
      </div>
    </div>
  </div>

  <!-- 維修紀錄 -->
  <div class="col-md-3">
    <div class="card dashboard-card h-100">
      <div class="card-body text-center d-flex flex-column justify-content-between">
        <div>
          <i class="fas fa-tools fa-2x mb-3 text-success"></i>
          <h5 class="card-title">維修紀錄</h5>
        </div>
        <a href="maintenance_records.php" class="btn btn-success mt-3">管理紀錄</a>
      </div>
    </div>
  </div>

  <!-- 客戶管理 -->
  <div class="col-md-3">
    <div class="card dashboard-card h-100">
      <div class="card-body text-center d-flex flex-column justify-content-between">
        <div>
          <i class="fas fa-users fa-2x mb-3 text-info"></i>
          <h5 class="card-title">客戶管理</h5>
        </div>
        <a href="staff_users.php" class="btn btn-info mt-3">管理客戶</a>
      </div>
    </div>
  </div>

  <!-- 新增估價單 -->
  <div class="col-md-3">
    <div class="card dashboard-card h-100">
      <div class="card-body text-center d-flex flex-column justify-content-between">
        <div>
          <i class="fas fa-plus-circle fa-2x mb-3 text-warning"></i>
          <h5 class="card-title">新增估價單</h5>
        </div>
        <a href="admin_add_part.php" class="btn btn-warning text-white mt-3">進入</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
        <div class="card dashboard-card h-100">
          <div class="card-body text-center d-flex flex-column justify-content-between">
            <i class="fas fa-file-invoice-dollar fa-2x mb-3 text-primary"></i>
            <h5 class="card-title">估價單狀態</h5>
            <a href="admin_view_estimates.php" class="btn btn-primary mt-3">進入查看</a>
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
  // 查詢最近預約
  $sql = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, v.license_plate, a.service_items, a.status
         FROM appointments a
         JOIN vehicles v ON a.vehicle_id = v.vehicle_id
         ORDER BY a.appointment_date DESC, a.appointment_time DESC";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      echo "<tr>";
      echo "<td>" . htmlspecialchars($row['appointment_date']) . "</td>";
      echo "<td>" . htmlspecialchars($row['appointment_time']) . "</td>";
      echo "<td>" . htmlspecialchars($row['license_plate']) . "</td>";

      // 中文維修項目
      $items = explode(',', $row['service_items']);
      $translated_items = [];
      foreach ($items as $item) {
        $item = trim($item);
        $translated_items[] = $service_item_map[$item] ?? $item;
      }
      echo "<td>" . htmlspecialchars(implode('、', $translated_items)) . "</td>";

      // 狀態顏色與文字
      $status = $row['status'];
      $statusText = $status_map[$status] ?? '';
      $statusClass = match($status) {
        'pending' => 'bg-warning',
        'confirmed' => 'bg-info',
        'repair' => 'bg-primary',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        'noshow' => 'bg-dark',
        default => 'bg-secondary'
      };
      echo "<td><span class='badge $statusClass status-badge'>$statusText</span></td>";

      // 操作按鈕
      echo "<td>";
      if ($status == 'pending') {
          echo "<button class='btn btn-sm btn-outline-primary' onclick='confirmAppointment(" . $row['appointment_id'] . ")'>確認</button> ";
          echo "<button class='btn btn-sm btn-outline-danger' onclick='cancelAppointment(" . $row['appointment_id'] . ")'>取消</button>";
      } elseif ($status == 'confirmed') {
        echo "<a href='admin_add_part.php?appointment_id=" . $row['appointment_id'] . "' class='btn btn-sm btn-outline-primary'>開始維修</a>";
      } elseif ($status == 'repair') {
          echo "<button class='btn btn-sm btn-outline-success' onclick='completeMaintenance(" . $row['appointment_id'] . ")'>完成維修</button>";
      }
      echo "</td></tr>";
    }
  } else {
    echo "<tr><td colspan='6' class='text-center'>最近無預約</td></tr>";
  }
  ?>
</tbody>

      </table>
    </div>

    <!-- ========== 待完成維修列表 ========== -->
    <h3 class="mb-4">待完成維修</h3>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>開始時間</th>
            <th>車牌號碼</th>
            <th>維修項目</th>
            <th>預計完成時間</th>
            <th>狀態</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
  <?php
  $sql = "SELECT a.appointment_id, a.appointment_time, v.license_plate, a.service_items, a.status
         FROM appointments a
         JOIN vehicles v ON a.vehicle_id = v.vehicle_id
         WHERE a.status = 'repair'
         ORDER BY a.appointment_time DESC";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      echo "<tr>";
      echo "<td>" . htmlspecialchars($row['appointment_time']) . "</td>";
      echo "<td>" . htmlspecialchars($row['license_plate']) . "</td>";

      // 中文維修項目
      $items = explode(',', $row['service_items']);
      $translated_items = [];
      foreach ($items as $item) {
        $item = trim($item);
        $translated_items[] = $service_item_map[$item] ?? $item;
      }
      echo "<td>" . htmlspecialchars(implode('、', $translated_items)) . "</td>";

      echo "<td>待完成</td>";

      $status = $row['status'];
      $statusText = $status_map[$status] ?? '';
      $statusClass = match($status) {
        'pending' => 'bg-warning',
        'confirmed' => 'bg-info',
        'repair' => 'bg-primary',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        default => 'bg-secondary'
      };
      echo "<td><span class='badge $statusClass status-badge'>$statusText</span></td>";

      echo "<td>";
      if ($status == 'repair') {
        echo "<button class='btn btn-sm btn-outline-success' onclick='completeMaintenance(" . $row['appointment_id'] . ")'>完成維修</button>";
      }
      echo "</td></tr>";
    }
  } else {
    echo "<tr><td colspan='6' class='text-center'>目前無待完成的維修工作</td></tr>";
  }
  ?>
</tbody>

      </table>
    </div>
  </div>

  <!-- ========== 頁尾資訊 ========== -->
  <footer class="bg-primary text-light py-4 text-center">
    <p>&copy; 2024-2025 睿煬企業社維修查詢系統 | 開發人員：林宸皓、陳彥丞、陳宗偉</p>
  </footer>

  <!-- ========== JavaScript 程式碼 ========== -->
  <!-- 引入必要的 JavaScript 函式庫 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  
  <!-- 預約狀態更新相關函數 -->
  <script>
  // 確認預約的函數
  function confirmAppointment(appointmentId) {
      if (confirm('確定要確認這個預約嗎？')) {
          // 發送 AJAX 請求到後端 API
          $.ajax({
              url: 'api/updateAppointmentStatus.php',
              type: 'POST',
              contentType: 'application/json',
              data: JSON.stringify({
                  appointment_id: appointmentId,
                  status: 'confirmed'
              }),
              success: function(response) {
                  if (response.success) {
                      alert('已確認預約！');
                      location.reload(); // 重新載入頁面以更新狀態
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

  // 開始維修的函數
  function startMaintenance(appointmentId) {
      if (confirm('確定要開始維修嗎？')) {
          // 發送 AJAX 請求到後端 API
          $.ajax({
              url: 'api/updateAppointmentStatus.php',
              type: 'POST',
              contentType: 'application/json',
              data: JSON.stringify({
                  appointment_id: appointmentId,
                  status: 'repair'
              }),
              success: function(response) {
                  if (response.success) {
                      alert('已開始維修！');
                      location.reload(); // 重新載入頁面以更新狀態
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

  // 完成維修的函數
  function completeMaintenance(appointmentId) {
      if (confirm('確定要完成維修嗎？')) {
          // 發送 AJAX 請求到後端 API
          $.ajax({
              url: 'api/updateAppointmentStatus.php',
              type: 'POST',
              contentType: 'application/json',
              data: JSON.stringify({
                  appointment_id: appointmentId,
                  status: 'completed'
              }),
              success: function(response) {
                  if (response.success) {
                      alert('維修已完成！');
                      location.reload();
                  } else {
                      alert('更新失敗：' + response.message);
                  }
              },
              error: function(xhr, status, error) {
                  console.error('Error:', error);
                  alert('系統錯誤，請稍後再試');
              }
          });
      } else {
          alert('維修已完成！');
      }
  }

  // 取消預約的函數
 function cancelAppointment(id) {
  Swal.fire({
    title: '確定要取消嗎？',
    text: '此操作將取消該筆預約並釋放名額',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '是，取消',
    cancelButtonText: '不',
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('api/updateAppointmentStatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          appointment_id: id,
          status: 'cancelled'
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire('已取消！', '該預約已取消並釋放名額', 'success');
          refreshTimeSlots(); // 這是你的自訂函式，用來更新時段人數
        } else {
          Swal.fire('失敗', data.message || '無法取消', 'error');
        }
      });
    }
  });
}
function updateStatus(id, status) {
  Swal.fire({
    title: '確定執行？',
    text: `狀態將更改為「${status}」`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '確定',
    cancelButtonText: '取消'
  }).then((result) => {
    if (result.isConfirmed) {
      fetch('api/updateAppointmentStatus.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          appointment_id: id,
          status: status
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire('✅ 成功更新', '', 'success').then(() => location.reload());
        } else {
          Swal.fire('❌ 操作失敗', data.message || '請稍後再試', 'error');
        }
      });
    }
  });
}
</script>
</body>
</html>
<?php
// 關閉資料庫連線
$conn->close();
?>
