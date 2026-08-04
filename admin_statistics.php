<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "karry,roy,jackson";
$dbName     = "睿煬企業社";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$date_filter = "";
if ($start_date && $end_date) {
  $date_filter = "WHERE appointment_date BETWEEN '$start_date' AND '$end_date'";
}

$sql_monthly = "SELECT DATE_FORMAT(appointment_date, '%Y-%m') AS month, COUNT(*) AS count
                FROM appointments $date_filter
                GROUP BY month ORDER BY month";
$result_monthly = $conn->query($sql_monthly);
$months = [];
$month_counts = [];
while ($row = $result_monthly->fetch_assoc()) {
    $months[] = $row['month'];
    $month_counts[] = $row['count'];
}

$sql_services = "SELECT service_items FROM appointments";
if (!empty($date_filter)) {
    $sql_services .= " $date_filter AND service_items IS NOT NULL";
} else {
    $sql_services .= " WHERE service_items IS NOT NULL";
}
$result_services = $conn->query($sql_services);
$service_counts = [];
$service_map = [
    'maintenance' => '一般檢修',
    'inspection' => '年度檢查',
    'cleaning' => '車輛清潔'
];
while ($row = $result_services->fetch_assoc()) {
    $items = explode(',', $row['service_items']);
    foreach ($items as $item) {
        $item = trim($item);
        if ($item !== '') {
            $service_counts[$item] = ($service_counts[$item] ?? 0) + 1;
        }
    }
}
$service_labels = [];
$service_values = [];
foreach ($service_counts as $key => $value) {
    $service_labels[] = $service_map[$key] ?? $key;
    $service_values[] = $value;
}

$status_stats = [
  'pending' => 0,
  'confirmed' => 0,
  'repair' => 0,
  'completed' => 0,
  'cancelled' => 0
];
$sqlStatusCount = "SELECT status, COUNT(*) as count FROM appointments $date_filter GROUP BY status";
$result = $conn->query($sqlStatusCount);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $status_stats[$row['status']] = (int)$row['count'];
  }
}

$popular_dates = [];
$sqlDates = "SELECT appointment_date, COUNT(*) AS total FROM appointments $date_filter GROUP BY appointment_date ORDER BY total DESC LIMIT 5";
$result = $conn->query($sqlDates);
while ($row = $result->fetch_assoc()) {
  $popular_dates[] = [
    'date' => $row['appointment_date'],
    'count' => $row['total']
  ];
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>預約統計 - 管理員系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f5f6fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #2c3e50;
    }
    .card {
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }
    .card-header {
      font-weight: bold;
    }
    .btn-back {
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
<div class="container mt-5">

  <!-- 返回管理員首頁按鈕 -->
  <div class="text-end btn-back">
    <a href="admin_dashboard.php" class="btn btn-secondary">
      <i class="fas fa-arrow-left me-1"></i> 返回管理員首頁
    </a>
  </div>

  <h2 class="text-center mb-4">預約與維修統計圖表</h2>

  <!-- 日期篩選器 -->
  <form method="get" class="row g-3 mb-4">
    <div class="col-md-4">
      <label for="start_date" class="form-label">起始日期</label>
      <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
    </div>
    <div class="col-md-4">
      <label for="end_date" class="form-label">結束日期</label>
      <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
    </div>
    <div class="col-md-4 align-self-end">
      <button type="submit" class="btn btn-primary w-100">套用篩選</button>
    </div>
  </form>

  <!-- 每月預約折線圖 -->
  <div class="card mb-4">
    <div class="card-header bg-primary text-white">每月預約數</div>
    <div class="card-body">
      <canvas id="monthlyChart" style="max-height: 300px;"></canvas>
    </div>
  </div>

  <!-- 服務項目統計圓餅圖 -->
  <div class="card mb-4">
    <div class="card-header bg-success text-white">服務項目統計</div>
    <div class="card-body">
      <canvas id="serviceChart" style="max-height: 300px;"></canvas>
    </div>
  </div>

  <!-- 狀態與熱門日期 -->
  <div class="row">
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header bg-secondary text-white">預約狀態比例圖</div>
        <div class="card-body">
          <canvas id="statusChart"></canvas>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card mb-4">
        <div class="card-header bg-dark text-white">熱門預約日期排行</div>
        <div class="card-body">
          <ol class="mb-0">
            <?php foreach ($popular_dates as $item): ?>
              <li><?= htmlspecialchars($item['date']) ?> - <?= $item['count'] ?> 筆</li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script>
new Chart(document.getElementById('monthlyChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($months) ?>,
    datasets: [{
      label: '預約數量',
      data: <?= json_encode($month_counts) ?>,
      borderColor: 'rgb(75, 192, 192)',
      tension: 0.3,
      fill: false
    }]
  },
  options: {
    responsive: true,
    plugins: {
      tooltip: {
        callbacks: {
          label: ctx => `預約數量：${ctx.raw} 筆`
        }
      }
    },
    scales: { y: { beginAtZero: true } }
  }
});

new Chart(document.getElementById('serviceChart'), {
  type: 'pie',
  data: {
    labels: <?= json_encode($service_labels) ?>,
    datasets: [{
      label: '服務次數',
      data: <?= json_encode($service_values) ?>,
      backgroundColor: [
        'rgba(255, 99, 132, 0.6)',
        'rgba(54, 162, 235, 0.6)',
        'rgba(255, 206, 86, 0.6)',
        'rgba(75, 192, 192, 0.6)'
      ]
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'top' },
      tooltip: {
        callbacks: {
          label: ctx => `${ctx.label}: ${ctx.raw} 次`
        }
      }
    }
  }
});

new Chart(document.getElementById('statusChart'), {
  type: 'doughnut',
  data: {
    labels: ['待確認', '已確認', '維修中', '維修完成', '已取消'],
    datasets: [{
      label: '預約狀態統計',
      data: [
        <?= $status_stats['pending'] ?>,
        <?= $status_stats['confirmed'] ?>,
        <?= $status_stats['repair'] ?>,
        <?= $status_stats['completed'] ?>,
        <?= $status_stats['cancelled'] ?>
      ],
      backgroundColor: [
        'rgba(255, 205, 86, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(153, 102, 255, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(255, 99, 132, 0.7)'
      ]
    }]
  },
  options: {
    responsive: true,
    plugins: {
      tooltip: {
        callbacks: {
          label: ctx => `${ctx.label}: ${ctx.raw} 筆`
        }
      }
    }
  }
});
</script>
</body>
</html>
