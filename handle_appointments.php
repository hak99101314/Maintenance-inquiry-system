<?php
// 顯示錯誤訊息（除錯用）
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
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

$is_admin = ($_SESSION['role'] === 'admin');

$sql = "SELECT 
            a.appointment_id,
            u.full_name AS name,
            u.contact_number AS phone,
            u.no_show_count,
            v.license_plate,
            a.service_items AS service,
            a.appointment_date,
            a.appointment_time,
            a.status,
            (SELECT COUNT(*) FROM appointment_blacklist b WHERE b.user_id = u.user_id) AS is_blacklisted
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
    'cancelled' => '已取消',
    'noshow' => '未到'
];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>預約管理 - 員工系統</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
 <!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-2 sidebar d-flex flex-column bg-dark text-white vh-100">
      <h4 class="p-3 text-center border-bottom">員工系統</h4>
      <a href="staff_dashboard.php" class="text-white p-2">首頁</a>
      <a href="staff_profile.php" class="text-white p-2">員工資料</a>
      <a href="staff_users.php" class="text-white p-2">客戶管理</a>
      <a href="handle_appointments.php" class="text-white p-2 bg-success">預約管理</a>
      <a href="staff_orders.php" class="text-white p-2">維修管理</a>
      <a href="logout.php" class="text-white p-2">登出</a>
    </div>
    <div class="col-md-10 p-4">
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
              <?php if ($is_admin): ?><th>黑名單</th><?php endif; ?>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php $today = date('Y-m-d'); ?>
            <?php foreach ($appointments as $a): ?>
              <tr>
                <td><?= htmlspecialchars($a['appointment_id']) ?></td>
                <td><?= htmlspecialchars($a['name']) ?></td>
                <td><?= htmlspecialchars($a['phone']) ?></td>
                <td><?= htmlspecialchars($a['license_plate']) ?></td>
                <td><?= htmlspecialchars($a['service_translated']) ?></td>
                <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                <td><?= htmlspecialchars($a['appointment_time']) ?></td>
                <td>
                  <?php
                    $badgeClass = match ($a['status']) {
                      'pending' => 'bg-warning',
                      'confirmed' => 'bg-info',
                      'repair' => 'bg-primary',
                      'completed' => 'bg-success',
                      'cancelled' => 'bg-danger',
                      'noshow' => 'bg-dark',
                      default => 'bg-secondary',
                    };
                    echo "<span class='badge $badgeClass'>" . ($status_map[$a['status']] ?? '未知') . "</span>";
                  ?>
                </td>
                <?php if ($is_admin): ?>
                <td>
                  <?= $a['is_blacklisted'] ? '<span class="text-danger fw-bold">是</span>' : '否' ?>
                </td>
                <?php endif; ?>
                <td>
  <?php if ($a['status'] === 'pending'): ?>
    <button class="btn btn-sm btn-success" onclick="updateStatus(<?= $a['id'] ?>, 'confirmed')">確認</button>
    <button class="btn btn-sm btn-outline-danger" onclick="cancelAppointment(<?= $row['appointment_id'] ?>)">取消</button>
    <?php if ($a['appointment_date'] === $today): ?>
      <button class="btn btn-sm btn-secondary" onclick="updateStatus(<?= $a['id'] ?>, 'noshow')">未到</button>
    <?php endif; ?>
  <?php elseif ($a['status'] === 'confirmed'): ?>
    <a href="admin_create_estimate.php?appointment_id=<?= $a['id'] ?>" class="btn btn-sm btn-primary">開始維修</a>
    <button class="btn btn-sm btn-outline-danger" onclick="cancelAppointment(<?= $row['appointment_id'] ?>)">取消</button>
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
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
          status: status,
          operator_id: currentUserId
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
</script>
</body>
</html>
