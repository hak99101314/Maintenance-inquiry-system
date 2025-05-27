<?php
// 顯示錯誤訊息（除錯用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "睿煬企業社";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
  die("資料庫連線失敗: " . $conn->connect_error);
}

$conditions = [];
if (!empty($_GET['search_name'])) {
  $name = $conn->real_escape_string($_GET['search_name']);
  $conditions[] = "u.full_name LIKE '%$name%'";
}
if (!empty($_GET['search_plate'])) {
  $plate = $conn->real_escape_string($_GET['search_plate']);
  $conditions[] = "v.license_plate LIKE '%$plate%'";
}
if (!empty($_GET['search_status'])) {
  $status = $conn->real_escape_string($_GET['search_status']);
  $conditions[] = "a.status = '$status'";
}
$whereClause = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$sql = "SELECT 
          a.appointment_id AS id,
          u.user_id,
          u.full_name AS name,
          u.contact_number AS phone,
          v.license_plate,
          a.service_items AS service,
          a.appointment_date,
          a.appointment_time,
          a.status,
          IF(ab.id IS NOT NULL, 1, 0) AS is_blacklisted
        FROM appointments a
        JOIN users u ON a.customer_id = u.user_id
        JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        LEFT JOIN appointment_blacklist ab ON ab.user_id = u.user_id
        $whereClause
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
} else {
  die("\u274c \u67e5\u8a62\u9810\u7d04\u5931\u6557：" . $conn->error);
}
$conn->close();

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

    .sidebar a:hover,
    .sidebar a.active {
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
        <h2>預約管理</h2>
        <form method="GET" class="row g-2 mb-3">
          <div class="col-md-3">
            <input type="text" name="search_name" class="form-control" placeholder="搜尋姓名" value="<?= htmlspecialchars($_GET['search_name'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <input type="text" name="search_plate" class="form-control" placeholder="搜尋車牌" value="<?= htmlspecialchars($_GET['search_plate'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <select name="search_status" class="form-select">
              <option value="">全部狀態</option>
              <?php foreach ($status_map as $k => $v): ?>
                <option value="<?= $k ?>" <?= (($_GET['search_status'] ?? '') === $k) ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <button class="btn btn-primary w-100">搜尋</button>
          </div>
        </form>
        <table class="table table-bordered text-center">
          <thead class="table-light">
            <tr>
              <th>編號</th>
              <th>姓名</th>
              <th>電話</th>
              <th>車牌</th>
              <th>服務項目</th>
              <th>日期</th>
              <th>時間</th>
              <th>狀態</th>
              <th>黑名單</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php $today = date('Y-m-d'); ?>
            <?php foreach ($appointments as $a): ?>
              <tr>
                <td><?= $a['id'] ?></td>
                <td><?= htmlspecialchars($a['name']) ?></td>
                <td><?= htmlspecialchars($a['phone']) ?></td>
                <td><?= htmlspecialchars($a['license_plate']) ?></td>
                <td><?= htmlspecialchars($a['service_translated']) ?></td>
                <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                <td><?= htmlspecialchars($a['appointment_time']) ?></td>
                <td><span class="badge <?= $status_class_map[$a['status']] ?? 'bg-secondary' ?>"><?= $status_map[$a['status']] ?? '未知' ?></span></td>
                <td>
                  <?= $a['is_blacklisted'] ? '<span class="text-danger fw-bold">是</span>' : '否' ?>
                  <?php if ($a['is_blacklisted']): ?>
                    <button class="btn btn-sm btn-outline-danger mt-1" onclick="unblockUser(<?= $a['user_id'] ?>)">解除</button>

                  <?php endif; ?>
                </td>
               <td>
  <?php if ($a['status'] === 'pending'): ?>
    <button class="btn btn-sm btn-success" onclick="updateStatus(<?= $a['id'] ?>, 'confirmed')">確認</button>
    <button class="btn btn-sm btn-outline-danger" onclick="cancelAppointment(<?= $a['id'] ?>)">取消</button>
    <?php if ($a['appointment_date'] === $today): ?>
      <button class="btn btn-sm btn-secondary" onclick="updateStatus(<?= $a['id'] ?>, 'noshow')">未到</button>
    <?php endif; ?>
  
  <?php elseif ($a['status'] === 'confirmed'): ?>
    <a href="admin_create_estimate.php?appointment_id=<?= $a['id'] ?>" class="btn btn-sm btn-primary">開始維修</a>
    <button class="btn btn-sm btn-outline-danger" onclick="cancelAppointment(<?= $a['id'] ?>)">取消</button>
    <?php if ($a['appointment_date'] === $today): ?>
      <button class="btn btn-sm btn-secondary" onclick="updateStatus(<?= $a['id'] ?>, 'noshow')">未到</button>
    <?php endif; ?>
  <?php endif; ?>
</td>

              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <!-- 引入 SweetAlert2 函式庫 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
  window.cancelAppointment = function(id) {
  console.log("正在執行取消邏輯 for appointment_id:", id); // debug 用

 Swal.fire({
    title: '確定要取消嗎？',
    text: '此操作將取消該筆預約並釋放名額',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '是，取消',
    cancelButtonText: '不'
  }).then((result) => {
    if (!result.isConfirmed) return;

    fetch('api/updateAppointmentStatus.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ appointment_id: id, status: 'cancelled' })
})
.then(res => res.text()) // 🔁 先拿原始文字
.then(text => {
  console.log("Raw response:", text); // ✅ 看這裡是否為純 JSON
  const data = JSON.parse(text);
  if (data.success) {
    Swal.fire('✅ 已更新', '', 'success').then(() => location.reload());
  } else {
    Swal.fire('操作失敗', data.message || '請稍後再試', 'error');
  }
})
.catch(err => {
  console.error("解析錯誤：", err);
  Swal.fire('系統錯誤', '請稍後再試', 'error');
})
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          icon: 'success',
          title: '已取消',
          text: '該預約已取消並釋放名額',
          timer: 1500,
          showConfirmButton: false
        });
        setTimeout(() => location.reload(), 1600);
      } else {
        Swal.fire('操作失敗', data.message || '請稍後再試', 'error');
      }
    })
    .catch(() => {
      Swal.fire('系統錯誤', '請稍後再試', 'error');
    });
  });
}
});

    function updateStatus(id, status) {
      if (confirm(`是否將狀態設為「${status}」？`)) {
        fetch('api/updateAppointmentStatus.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            appointment_id: id,
            status: status
          })
        }).then(r => r.json()).then(data => {
          if (data.success) {
            alert('狀態已更新');
            location.reload();
          } else {
            alert('更新失敗：' + data.message);
          }
        });
      }
    }

    function startRepair(id, licensePlate) {
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

    function unblockUser(userId) {
  fetch('api/unblockUser.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ user_id: userId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('✅ 已成功解除黑名單');
      location.reload();
    } else {
      alert('❌ 解除失敗：' + data.message);
    }
  });
}

  </script>
</body>

</html>