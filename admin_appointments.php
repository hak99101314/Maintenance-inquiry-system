<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson";
$dbName = "睿煬企業社";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

$sql = "SELECT 
            a.appointment_id AS id,
            u.full_name AS name,
            u.contact_number AS phone,
            v.license_plate,
            a.service_items AS service,
            a.appointment_date,
            a.appointment_time,
            a.status
        FROM appointments a
        JOIN users u ON a.customer_id = u.user_id
        JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$result = $conn->query($sql);
$appointments = [];
if ($result) {
    $service_item_map = [
        'maintenance' => '一般檢修',
        'inspection' => '年度檢查',
        'cleaning' => '車輛清潔'
    ];
    while ($row = $result->fetch_assoc()) {
        $items = explode(',', $row['service']);
        $translated = [];
        foreach ($items as $item) {
            $item = trim($item);
            $translated[] = $service_item_map[$item] ?? $item;
        }
        $row['service_translated'] = implode('、', $translated);
        $appointments[] = $row;
    }
}
$conn->close();

$status_map = [
    'pending' => '待確認',
    'confirmed' => '已確認',
    'repair' => '維修中',
    'completed' => '維修完成',
    'cancelled' => '已取消'
];
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>預約管理 - 管理員系統</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f9;
    }
    .sidebar {
      height: 100vh;
      background-color: #2c3e50;
      color: white;
    }
    .sidebar a {
      color: #ecf0f1;
      display: block;
      padding: 1rem;
      text-decoration: none;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #1abc9c;
    }
    .main-content {
      padding: 2rem;
    }
  </style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-2 sidebar d-flex flex-column">
      <h4 class="p-3 text-center border-bottom">管理員系統</h4>
      <a href="admin_dashboard.php"><i class="fas fa-home me-2"></i>首頁</a>
      <a href="admin_profile.php"><i class="fas fa-id-badge me-2"></i>管理員資料</a>
      <a href="admin_users.php"><i class="fas fa-users me-2"></i>用戶管理</a>
      <a href="admin_appointments.php" class="active"><i class="fas fa-calendar-alt me-2"></i>預約管理</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>登出</a>
    </div>

    <div class="col-md-10 main-content">
      <h3 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>預約管理</h3>

      <div class="table-responsive bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered align-middle text-center">
          <thead class="table-secondary">
            <tr>
              <th>編號</th>
              <th>客戶姓名</th>
              <th>電話</th>
              <th>車牌</th>
              <th>服務項目</th>
              <th>日期</th>
              <th>時間</th>
              <th>狀態</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($appointments as $appointment): ?>
              <tr>
                <td><?= htmlspecialchars($appointment['id']) ?></td>
                <td><?= htmlspecialchars($appointment['name']) ?></td>
                <td><?= htmlspecialchars($appointment['phone']) ?></td>
                <td><?= htmlspecialchars($appointment['license_plate']) ?></td>
                <td><?= htmlspecialchars($appointment['service_translated']) ?></td>
                <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                <td>
                  <?php
                    $badgeClass = match ($appointment['status']) {
                      'pending' => 'bg-warning',
                      'confirmed' => 'bg-info',
                      'repair' => 'bg-primary',
                      'completed' => 'bg-success',
                      'cancelled' => 'bg-danger',
                      default => 'bg-secondary',
                    };
                    echo "<span class='badge $badgeClass'>" . ($status_map[$appointment['status']] ?? '未知') . "</span>";
                  ?>
                </td>
                <td>
                  <?php if ($appointment['status'] === 'pending'): ?>
                    <button class="btn btn-sm btn-success" onclick="updateStatus(<?= $appointment['id'] ?>, 'confirmed')">確認</button>
                    <button class="btn btn-sm btn-danger" onclick="updateStatus(<?= $appointment['id'] ?>, 'cancelled')">取消</button>
                  <?php else: ?>
                    <span class="text-muted">--</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap + AJAX 狀態更新 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(appointmentId, status) {
  if (confirm('確定要更新預約狀態嗎？')) {
    fetch('api/updateAppointmentStatus.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ appointment_id: appointmentId, status: status })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('狀態更新成功！');
        location.reload();
      } else {
        alert('更新失敗：' + data.message);
      }
    })
    .catch(error => {
      console.error('錯誤：', error);
      alert('系統錯誤，請稍後再試');
    });
  }
}
</script>
</body>
</html>
