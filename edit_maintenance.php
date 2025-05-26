<?php
// ========== 檔案說明 ==========
// maintenance_details.php 員工系統 - 維修紀錄詳情頁面（正規化資料表）

session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    die("<p style='color:red;'>請先登入。</p>");
}

$record_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($record_id <= 0) {
    die("缺少維修單號，無法顯示紀錄。");
}

$conn = new mysqli("localhost", "root", "karry,roy,jackson", "睿煬企業社");
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// 查主紀錄、車輛、車主資訊（透過 JOIN）
$sql = "SELECT mr.*, v.license_plate, v.brand, v.model, v.year,
               u.full_name AS owner_name, u.contact_number
        FROM maintenance_records mr
        JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
        JOIN users u ON v.owner_id = u.user_id
        WHERE mr.record_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL 準備錯誤: " . $conn->error);
}
$stmt->bind_param("i", $record_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("找不到維修紀錄。");
}
$record = $result->fetch_assoc();
$stmt->close();

// 查維修細項
$stmt2 = $conn->prepare("SELECT item_name AS repair_item, quantity, unit_price, 
                                (quantity * unit_price) AS subtotal, '' AS specification, '' AS notes
                         FROM maintenance_items
                         WHERE record_id = ?");
$stmt2->bind_param("i", $record_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$items = [];
while ($row = $result2->fetch_assoc()) {
    $items[] = $row;
}
$stmt2->close();
$conn->close();

$role = $_SESSION['role'];
$homeUrl = $role === 'admin' ? 'admin_dashboard.php' : ($role === 'staff' ? 'staff_dashboard.php' : 'dashboard.php');
$returnUrl = $role === 'admin' ? 'admin_maintenance.php' : ($role === 'staff' ? 'maintenance_records.php' : 'maintenance-history.php');
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>維修紀錄詳情 - 維修查詢系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #2c3e50; font-size: 18px; }
        .navbar { background-color: #2c3e50; }
        .navbar-brand, .nav-link { color: #ffffff !important; font-weight: bold; }
        .nav-link:hover { color: #f1c40f !important; }
        h4.card-title { color: #2c3e50; font-weight: bold; border-bottom: 2px solid #f1c40f; padding-bottom: 5px; font-size: 22px; }
        h5 { font-size: 20px; margin-top: 20px; }
        .card { border-radius: 10px; box-shadow: 0 0 15px rgba(0, 0, 0, 0.05); background-color: #ffffff; }
        .table thead th, .table td { background-color: #fff; color: #2c3e50; font-size: 18px; }
        .table thead th { background-color: #ecf0f1; }
        .text-danger { font-size: 1.3rem; font-weight: bold; }
        strong { color: #34495e; }
        hr { border-top: 2px solid #f1c40f; }
        .btn-secondary { font-size: 16px; padding: 10px 20px; border-radius: 6px; }
        .btn-secondary:hover { background-color: #f1c40f; color: #2c3e50; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $homeUrl ?>"><i class="fas fa-tools me-2"></i>維修查詢</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= $homeUrl ?>">首頁</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?= $returnUrl ?>">維修紀錄查詢</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container my-5">
    <div class="card p-4">
        <h4 class="card-title text-center">維修紀錄詳細內容</h4>
        <div class="row mb-3">
            <div class="col-md-6"><strong>維修單號：</strong> <?= htmlspecialchars($record['record_id']) ?></div>
            <div class="col-md-6"><strong>維修日期：</strong> <?= htmlspecialchars($record['repair_date']) ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><strong>車主：</strong> <?= htmlspecialchars($record['owner_name']) ?></div>
            <div class="col-md-6"><strong>電話：</strong> <?= htmlspecialchars($record['contact_number']) ?></div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6"><strong>車牌號碼：</strong> <?= htmlspecialchars($record['license_plate']) ?></div>
            <div class="col-md-6"><strong>品牌 / 型號：</strong> <?= htmlspecialchars($record['brand'] . ' / ' . $record['model']) ?></div>
        </div>
        <div class="mb-3"><strong>年份：</strong> <?= htmlspecialchars($record['year']) ?>，<strong>公里數：</strong> <?= htmlspecialchars($record['mileage']) ?></div>
        <hr>
        <h5>建議事項</h5>
        <p><?= nl2br(htmlspecialchars($record['recommendations'])) ?></p>
        <h5>客戶簽名</h5>
        <p><?= htmlspecialchars($record['customer_signature']) ?></p>
        <div class="d-flex justify-content-end">
            <h5>總計費用： <span class="text-danger">$<?= htmlspecialchars($record['total_cost']) ?></span></h5>
        </div>
        <hr>
        <h5>維修項目清單</h5>
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>維修項目</th>
                    <th>規格</th>
                    <th>數量</th>
                    <th>單價</th>
                    <th>小計</th>
                    <th>備註</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($items) > 0): ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['repair_item']) ?></td>
                            <td><?= htmlspecialchars($item['specification']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= htmlspecialchars($item['unit_price']) ?></td>
                            <td><?= htmlspecialchars($item['subtotal']) ?></td>
                            <td><?= htmlspecialchars($item['notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">無維修項目</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center mt-4">
            <a href="<?= $returnUrl ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>返回維修紀錄
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
