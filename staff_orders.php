<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
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

$service_item_map = [
    'maintenance' => '一般檢查',
    'inspection' => '年度檢查',
    'cleaning' => '車輛清潔',
    'brake' => '煞車檢查',
    'engine' => '引擎維修'
];

$sql = "SELECT * FROM repair_orders ORDER BY repair_date DESC";
$result = $conn->query($sql);
$orders = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>維修管理 - 維修查詢系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(to bottom right, #f0f4f8, #ffffff);
      font-size: 17px;
    }
    .container {
      margin-top: 60px;
      background-color: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .btn-primary {
      background-color: #3f51b5;
      border: none;
    }
    .btn-primary:hover {
      background-color: #303f9f;
    }
    .btn-outline-primary {
      border-color: #3f51b5;
      color: #3f51b5;
    }
    .btn-outline-primary:hover {
      background-color: #3f51b5;
      color: white;
    }
    footer {
      background-color: #3f51b5;
    }
    .navbar {
      background-color: #3f51b5;
    }
    .table thead {
      background-color: #c5cae9;
    }
    h2 {
      color: #3f51b5;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <i class="fas fa-wrench me-2"></i>員工管理系統
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="staff_dashboard.php">首頁</a></li>
        <li class="nav-item"><a class="nav-link" href="staff_profile.php">員工資料</a></li>
        <li class="nav-item"><a class="nav-link" href="staff_users.php">客戶管理</a></li>
        <li class="nav-item"><a class="nav-link active" href="staff_orders.php">維修管理</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <h2 class="text-center mb-4">維修管理</h2>
  <div class="text-end mb-3">
    <a href="admin_record.php?new=1" class="btn btn-primary">新增維修紀錄</a>
  </div>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>訂單ID</th>
        <th>客戶姓名</th>
        <th>維修日期</th>
        <th>維修項目</th>
        <th>狀態</th>
        <th>操作</th>
      </tr>
    </thead>
    <tbody>
      <?php if(count($orders) > 0): ?>
        <?php foreach($orders as $order): ?>
          <tr>
            <td><?= htmlspecialchars($order['id']) ?></td>
            <td><?= htmlspecialchars($order['owner']) ?></td>
            <td><?= htmlspecialchars($order['repair_date']) ?></td>
            <td>
              <?php
                $items = explode(',', $order['service_items'] ?? '');
                $translated = [];
                foreach ($items as $item) {
                    $item = trim($item);
                    if ($item !== '') {
                        $translated[] = $service_item_map[$item] ?? $item;
                    }
                }
                echo $translated ? htmlspecialchars(implode('、', $translated)) : '<span class="text-muted">未填寫</span>';
              ?>
            </td>
            <td><?= htmlspecialchars($order['status'] ?? '待處理') ?></td>
            <td>
              <a href="maintenance_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">查看</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="6" class="text-center">無維修紀錄</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<footer class="text-light py-4 text-center">
  <p>&copy; 2024 維修查詢系統 ｜ 協作單位：睿煬企業社、康寧大學資管科 17.林宸皓 13.陳彥丞 19.陳宗偉</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>