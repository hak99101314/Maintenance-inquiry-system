<?php
session_start();

// 權限驗證：僅限登入後
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 連線資料庫
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 取得維修紀錄 ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("未提供維修紀錄 ID。");
}
$record_id = intval($_GET['id']);

// 查詢主紀錄資料
$sql_record = "
    SELECT mr.*, v.license_plate, v.model, u.full_name
    FROM maintenance_records mr
    LEFT JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
    LEFT JOIN users u ON v.owner_id = u.user_id
    WHERE mr.record_id = $record_id
";
$record_result = $conn->query($sql_record);
if ($record_result->num_rows === 0) {
    die("找不到該筆維修紀錄。");
}
$record = $record_result->fetch_assoc();

// 查詢該筆的維修項目明細
$sql_items = "
    SELECT item_name, quantity, unit_price
    FROM maintenance_items
    WHERE record_id = $record_id
";
$items_result = $conn->query($sql_items);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>維修詳情</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 30px; font-family: "Segoe UI", sans-serif; background-color: #f8f9fa; }
        .card { margin-bottom: 20px; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4 text-center">維修紀錄詳情</h2>

    <div class="card">
        <div class="card-header bg-dark text-white">車主與維修資訊</div>
        <div class="card-body">
            <p><strong>車主：</strong> <?= htmlspecialchars($record['full_name']) ?></p>
            <p><strong>車牌號碼：</strong> <?= htmlspecialchars($record['license_plate']) ?></p>
            <p><strong>車型：</strong> <?= htmlspecialchars($record['model']) ?></p>
            <p><strong>維修日期：</strong> <?= htmlspecialchars($record['repair_date']) ?></p>
            <p><strong>里程數：</strong> <?= htmlspecialchars($record['mileage']) ?> km</p>
            <p><strong>建議事項：</strong> <?= nl2br(htmlspecialchars($record['recommendations'])) ?></p>
            <p><strong>客戶簽名：</strong> <?= htmlspecialchars($record['customer_signature']) ?></p>
            <p><strong>總金額：</strong> <span class="text-danger fw-bold">$<?= htmlspecialchars($record['total_cost']) ?></span></p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">維修項目明細</div>
        <div class="card-body">
            <?php if ($items_result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>項目名稱</th>
                        <th>數量</th>
                        <th>單價</th>
                        <th>小計</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td>$<?= htmlspecialchars($item['unit_price']) ?></td>
                            <td>$<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="text-muted">無維修項目紀錄。</p>
            <?php endif; ?>
        </div>
    </div>

    <a href="admin_maintenance.php" class="btn btn-secondary">返回列表</a>
</div>
</body>
</html>
<?php $conn->close(); ?>
