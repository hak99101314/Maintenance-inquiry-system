<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "karry,roy,jackson", "睿煬企業社");
if ($conn->connect_error) die("連線失敗：" . $conn->connect_error);

// 查詢本月或選定月份所有預約
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$firstDay = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$lastDay = date("Y-m-t", strtotime($firstDay));

$sql = "SELECT appointment_date, appointment_time, service_items, v.license_plate, u.full_name
        FROM appointments a
        JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        JOIN users u ON v.owner_id = u.user_id
        WHERE appointment_date BETWEEN ? AND ?
        ORDER BY appointment_date, appointment_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $firstDay, $lastDay);
$stmt->execute();
$result = $stmt->get_result();

$appointmentsByDay = [];
while ($row = $result->fetch_assoc()) {
    $appointmentsByDay[$row['appointment_date']][] = $row;
}
$stmt->close();
$conn->close();

$backTo = $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'staff_dashboard.php';
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>📅 本月排程模擬圖</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .day-cell {
      border: 1px solid #dee2e6;
      border-radius: 6px;
      padding: 1rem;
      transition: all 0.3s;
      cursor: pointer;
      background: #fff;
      min-height: 70px;
    }
    .day-cell:hover {
      background-color: #fef6d3;
    }
    .has-appointment {
      background-color: #fef3c7;
      border-left: 5px solid #f59e0b;
    }
    .calendar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    .view-switcher .btn {
      margin-left: 5px;
    }
    .list-view, .week-view {
      display: none;
    }
    .active-view {
      display: block !important;
    }
  </style>
</head>
<body>
<div class="container my-5">
  <div class="calendar-header">
    <h3>📅 <?= $year ?> 年 <?= $month ?> 月排程</h3>
    <div>
      <a href="?year=<?= $month == 1 ? $year - 1 : $year ?>&month=<?= $month == 1 ? 12 : $month - 1 ?>" class="btn btn-outline-secondary">← 上月</a>
      <a href="?year=<?= $month == 12 ? $year + 1 : $year ?>&month=<?= $month == 12 ? 1 : $month + 1 ?>" class="btn btn-outline-secondary">下月 →</a>
      <div class="view-switcher d-inline">
        <button class="btn btn-primary" onclick="switchView('calendar')">月曆</button>
        <button class="btn btn-outline-primary" onclick="switchView('week')">週曆</button>
        <button class="btn btn-outline-primary" onclick="switchView('list')">列表</button>
      </div>
    </div>
  </div>

  <!-- 月曆 -->
  <div id="calendar-view" class="calendar-view active-view">
    <div class="row">
      <?php
      $daysInMonth = date('t', strtotime("$year-$month-01"));
      for ($d = 1; $d <= $daysInMonth; $d++) {
          $date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
          $hasAppointment = isset($appointmentsByDay[$date]);
          echo '<div class="col-md-3 mb-3">';
          echo '<div class="day-cell ' . ($hasAppointment ? 'has-appointment' : '') . '" onclick="showAppointments(\'' . $date . '\')">';
          echo "<strong>$date</strong><br>";
          echo $hasAppointment ? count($appointmentsByDay[$date]) . " 筆預約" : "無預約";
          echo '</div></div>';
      }
      ?>
    </div>
  </div>

  <!-- 列表視圖 -->
  <div id="list-view" class="list-view">
    <h4>📋 本月預約列表</h4>
    <?php if (empty($appointmentsByDay)) echo "<p>本月尚無預約資料。</p>"; ?>
    <ul class="list-group">
      <?php foreach ($appointmentsByDay as $date => $items): ?>
        <li class="list-group-item active"><?= $date ?></li>
        <?php foreach ($items as $a): ?>
          <li class="list-group-item">
            ⏰ <?= $a['appointment_time'] ?>｜🚗 <?= $a['license_plate'] ?>｜👤 <?= $a['full_name'] ?>｜🔧 <?= $a['service_items'] ?>
          </li>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- 週曆（簡化版） -->
  <div id="week-view" class="week-view">
    <h4>📅 本週排程</h4>
    <ul class="list-group">
      <?php
      $weekStart = date('Y-m-d', strtotime("last sunday"));
      for ($i = 0; $i < 7; $i++) {
          $current = date('Y-m-d', strtotime("+$i days", strtotime($weekStart)));
          echo "<li class='list-group-item'>";
          echo "<strong>$current</strong>：";
          if (isset($appointmentsByDay[$current])) {
              echo count($appointmentsByDay[$current]) . " 筆預約";
          } else {
              echo "無預約";
          }
          echo "</li>";
      }
      ?>
    </ul>
  </div>

  <a href="<?= $backTo ?>" class="btn btn-secondary mt-4">← 返回首頁</a>
</div>

<!-- 詳細彈跳視窗 -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="appointmentModalLabel">預約詳情</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="appointmentDetail"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const appointments = <?= json_encode($appointmentsByDay, JSON_UNESCAPED_UNICODE) ?>;

function showAppointments(date) {
  const appointments = <?= json_encode($appointmentsByDay, JSON_UNESCAPED_UNICODE) ?>;

function showAppointments(date) {
    const container = document.getElementById('appointmentDetail');
    container.innerHTML = '';
    if (!appointments[date]) {
        container.innerHTML = '<p>這天尚無預約。</p>';
    } else {
        let html = '<ul class="list-group">';
        appointments[date].forEach(item => {
            const serviceMap = {
                'maintenance': '🔧 一般檢查',
                'inspection': '🔍 年度檢查',
                'cleaning': '🧼 車輛清潔'
            };
            const translatedItems = item.service_items
                .split(',')
                .map(s => serviceMap[s.trim()] || s)
                .join('、');

            html += `<li class="list-group-item">
                      ⏰ <strong>${item.appointment_time}</strong>｜
                      🚗 ${item.license_plate}｜
                      👤 ${item.full_name}｜
                      ${translatedItems}
                    </li>`;
        });
        html += '</ul>';
        container.innerHTML = html;
    }

    const modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
    modal.show();
}

}

function switchView(view) {
  document.getElementById('calendar-view').classList.remove('active-view');
  document.getElementById('list-view').classList.remove('active-view');
  document.getElementById('week-view').classList.remove('active-view');
  document.getElementById(view + '-view').classList.add('active-view');
}
</script>
</body>
</html>
