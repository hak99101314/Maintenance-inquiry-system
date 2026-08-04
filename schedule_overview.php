<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "karry,roy,jackson", "睿煬企業社");
if ($conn->connect_error) die("連線失敗：" . $conn->connect_error);

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date("Y-m-t", strtotime($startDate));
$brandFilter = isset($_GET['brand']) ? $_GET['brand'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT appointment_date, appointment_time, service_items, v.license_plate, u.full_name, v.brand, a.status
        FROM appointments a
        JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        JOIN users u ON v.owner_id = u.user_id
        WHERE appointment_date BETWEEN ? AND ?";
$params = [$startDate, $endDate];
$types = "ss";

if ($brandFilter !== '') {
    $sql .= " AND v.brand = ?";
    $types .= "s";
    $params[] = $brandFilter;
}
if ($statusFilter !== '') {
    $sql .= " AND a.status = ?";
    $types .= "s";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY appointment_date, appointment_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$appointmentsByDay = [];
$serviceCount = ['maintenance' => 0, 'inspection' => 0, 'cleaning' => 0];
while ($row = $result->fetch_assoc()) {
    $appointmentsByDay[$row['appointment_date']][] = $row;
    foreach (explode(',', $row['service_items']) as $s) {
        $s = trim($s);
        if (isset($serviceCount[$s])) $serviceCount[$s]++;
    }
}
$stmt->close();

$brands = [];
$res = $conn->query("SELECT DISTINCT brand FROM vehicles WHERE brand IS NOT NULL AND brand != '' ORDER BY brand");
while ($b = $res->fetch_assoc()) $brands[] = $b['brand'];
$res->close();

$conn->close();
$backTo = $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'staff_dashboard.php';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>📅 排程報表</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .day-cell {
      border: 1px solid #dee2e6; border-radius: 6px; padding: 1rem;
      transition: all 0.3s; cursor: pointer; background: #fff; min-height: 70px;
    }
    .day-cell:hover { background-color: #fef6d3; }
    .has-appointment { background-color: #fef3c7; border-left: 5px solid #f59e0b; }
    .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .view-switcher .btn { margin-left: 5px; }
    .list-view, .week-view, .calendar-view { display: none; }
    .active-view { display: block !important; }
  </style>
</head>
<body>
<div class="container my-4">
  <h3 class="mb-3">📅 <?= $year ?> 年 <?= $month ?> 月排程總覽</h3>
  <form class="row g-3 mb-4">
    <div class="col-md-3">
      <label class="form-label">品牌</label>
      <select name="brand" class="form-select">
        <option value="">全部</option>
        <?php foreach ($brands as $b): ?>
          <option value="<?= $b ?>" <?= $brandFilter === $b ? 'selected' : '' ?>><?= $b ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">狀態</label>
      <select name="status" class="form-select">
        <option value="">全部</option>
        <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>待確認</option>
        <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>已確認</option>
        <option value="repair" <?= $statusFilter === 'repair' ? 'selected' : '' ?>>維修中</option>
        <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>已完成</option>
        <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>已取消</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">開始日期</label>
      <input type="date" name="start_date" value="<?= $startDate ?>" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">結束日期</label>
      <input type="date" name="end_date" value="<?= $endDate ?>" class="form-control">
    </div>
    <div class="col-12">
      <button class="btn btn-primary">🔍 查詢</button>
      <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">重設</a>
    </div>
  </form>

  <h5 class="mb-3">📊 預約項目統計</h5>
  <canvas id="serviceChart" height="150"></canvas>

  <div class="calendar-header mt-4">
    <div class="view-switcher">
      <button class="btn btn-primary" onclick="switchView('calendar', this)">月曆</button>
      <button class="btn btn-outline-primary" onclick="switchView('week', this)">週曆</button>
      <button class="btn btn-outline-primary" onclick="switchView('list', this)">列表</button>
    </div>
  </div>

  <!-- 月曆視圖 -->
  <div id="calendar-view" class="calendar-view active-view">
    <div class="row">
      <?php
      $daysInMonth = date('t', strtotime("$year-$month-01"));
      for ($d = 1; $d <= $daysInMonth; $d++) {
          $date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
          $has = isset($appointmentsByDay[$date]);
          echo '<div class="col-md-3 mb-3">';
          echo '<div class="day-cell ' . ($has ? 'has-appointment' : '') . '" onclick="showAppointments(\'' . $date . '\')">';
          echo "<strong>$date</strong><br>";
          echo $has ? count($appointmentsByDay[$date]) . " 筆預約" : "無預約";
          echo '</div></div>';
      }
      ?>
    </div>
  </div>

  <!-- 列表視圖 -->
  <div id="list-view" class="list-view">
    <ul class="list-group">
      <?php foreach ($appointmentsByDay as $date => $items): ?>
        <li class="list-group-item active">📆 <?= $date ?></li>
        <?php foreach ($items as $a): ?>
          <li class="list-group-item">
            ⏰ <?= $a['appointment_time'] ?>｜🚗 <?= $a['license_plate'] ?>｜👤 <?= $a['full_name'] ?>｜🔧 <?= $a['service_items'] ?>
          </li>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- 週曆視圖 -->
  <div id="week-view" class="week-view">
    <ul class="list-group">
      <?php
      $weekStart = date('Y-m-d', strtotime("last sunday"));
      for ($i = 0; $i < 7; $i++) {
          $current = date('Y-m-d', strtotime("+$i days", strtotime($weekStart)));
          echo "<li class='list-group-item'><strong>$current</strong>：";
          echo isset($appointmentsByDay[$current]) ? count($appointmentsByDay[$current]) . " 筆預約" : "無預約";
          echo "</li>";
      }
      ?>
    </ul>
  </div>

  <a href="<?= $backTo ?>" class="btn btn-secondary mt-4">← 返回首頁</a>
</div>

<!-- 詳細彈窗 -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">預約詳情</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="appointmentDetail"></div>
    </div>
  </div>
</div>

<script>
const appointments = <?= json_encode($appointmentsByDay, JSON_UNESCAPED_UNICODE) ?>;

function switchView(view, btn) {
  ['calendar', 'list', 'week'].forEach(id => {
    document.getElementById(id + '-view').classList.remove('active-view');
  });
  document.getElementById(view + '-view').classList.add('active-view');
  document.querySelectorAll('.view-switcher .btn').forEach(b => b.classList.replace('btn-primary', 'btn-outline-primary'));
  btn.classList.replace('btn-outline-primary', 'btn-primary');
}

function showAppointments(date) {
  const container = document.getElementById('appointmentDetail');
  container.innerHTML = '';
  if (!appointments[date]) {
    container.innerHTML = '<p>這天尚無預約。</p>';
  } else {
    let html = '<ul class="list-group">';
    const map = { maintenance: '🔧 一般檢查', inspection: '🔍 年度檢查', cleaning: '🧼 車輛清潔' };
    appointments[date].forEach(item => {
      const items = item.service_items.split(',').map(s => map[s.trim()] || s).join('、');
      html += `<li class="list-group-item">⏰ <strong>${item.appointment_time}</strong>｜🚗 ${item.license_plate}｜👤 ${item.full_name}｜${items}</li>`;
    });
    html += '</ul>';
    container.innerHTML = html;
  }
  new bootstrap.Modal(document.getElementById('appointmentModal')).show();
}

new Chart(document.getElementById('serviceChart').getContext('2d'), {
  type: 'pie',
  data: {
    labels: ['一般檢查', '年度檢查', '車輛清潔'],
    datasets: [{
      data: [<?= $serviceCount['maintenance'] ?>, <?= $serviceCount['inspection'] ?>, <?= $serviceCount['cleaning'] ?>],
      backgroundColor: ['#4CAF50', '#FFC107', '#03A9F4']
    }]
  },
  options: {
    responsive: true,
    plugins: { title: { display: true, text: '服務項目占比' }, legend: { position: 'bottom' } }
  }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
