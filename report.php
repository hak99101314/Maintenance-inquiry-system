<?php
session_start();
require 'db_connect.php'; // 確保這個檔案存在

// 檢查是否登入
if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('請先登入'); window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION["user_id"];

// 查詢會員的維修紀錄（maintenance_records）
$sql = "SELECT mr.record_id AS id, v.license_plate, v.model AS car_model, mr.repair_date, mr.total_cost
        FROM maintenance_records mr
        JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
        WHERE v.owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 查詢所有維修項目（maintenance_items）
$sql = "SELECT mi.record_id AS order_id, mi.item_name AS repair_item, mi.quantity, mi.unit_price,
               (mi.quantity * mi.unit_price) AS subtotal
        FROM maintenance_items mi
        JOIN maintenance_records mr ON mi.record_id = mr.record_id
        JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
        WHERE v.owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// 整理維修項目資料以供圓餅圖使用
$item_costs = [];
foreach ($items as $item) {
    if (!empty($item['repair_item']) && is_numeric($item['subtotal'])) {
        if (!isset($item_costs[$item['repair_item']])) {
            $item_costs[$item['repair_item']] = 0;
        }
        $item_costs[$item['repair_item']] += (float) $item['subtotal'];
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員維修報表</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          background-color: #f8f9fa;
          color: #2c3e50;
          font-size: 17px;
          margin: 20px;
        }
        .container {
          max-width: 1000px;
          margin: auto;
          background: white;
          padding: 30px;
          box-shadow: 0 0 20px rgba(0,0,0,0.05);
          border-radius: 12px;
        }
        h2, h3 {
          text-align: center;
          font-weight: bold;
          color: #2c3e50;
          border-bottom: 2px solid #f1c40f;
          padding-bottom: 8px;
          margin-bottom: 30px;
        }
        table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 40px;
        }
        th, td {
          border: 1px solid #ddd;
          padding: 12px;
          text-align: center;
        }
        th {
          background-color: #2c3e50;
          color: white;
          font-weight: bold;
        }
        tr:hover {
          background-color: #f1f1f1;
        }
        canvas {
          max-width: 600px;
          margin: auto;
          display: block;
        }
        .btn-container {
          text-align: center;
          margin-top: 30px;
        }
        .btn {
          background-color: #2c3e50;
          color: white;
          font-size: 16px;
          padding: 12px 24px;
          border: none;
          border-radius: 8px;
          text-decoration: none;
          transition: background-color 0.3s ease;
        }
        .btn:hover {
          background-color: #f1c40f;
          color: #2c3e50;
          font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>會員維修報表</h2>

        <h3>維修單</h3>
        <table>
            <thead>
                <tr>
                    <th>維修單編號</th>
                    <th>車牌號碼</th>
                    <th>車型</th>
                    <th>維修日期</th>
                    <th>總費用 (NTD)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) { ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']); ?></td>
                    <td><?= htmlspecialchars($order['license_plate']); ?></td>
                    <td><?= htmlspecialchars($order['car_model']); ?></td>
                    <td><?= htmlspecialchars($order['repair_date']); ?></td>
                    <td><?= number_format($order['total_cost'], 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <h3>維修項目</h3>
        <table>
            <thead>
                <tr>
                    <th>維修單編號</th>
                    <th>項目名稱</th>
                    <th>數量</th>
                    <th>單價 (NTD)</th>
                    <th>小計 (NTD)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($items) === 0): ?>
                <tr><td colspan="5" class="text-center text-muted">尚無維修項目</td></tr>
                <?php else: foreach ($items as $item) { ?>
                <tr>
                    <td><?= htmlspecialchars($item['item_id']); ?></td>
                    <td><?= htmlspecialchars($item['item_name']); ?></td>
                    <td><?= htmlspecialchars($item['quantity']); ?></td>
                    <td><?= number_format($item['unit_price'], 2); ?></td>
                    <td><?= number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php } endif; ?>
            </tbody>
        </table>

        <?php if (count($item_costs) > 0): ?>
        <h3>維修費用比例</h3>
        <div style="width:100%; max-width:700px; margin:auto;"><canvas id="repairChart" height="400"></canvas></div>
        <?php else: ?>
        <p style="text-align:center; color:#888">尚無可統計的維修項目資料</p>
        <?php endif; ?>

        <div class="btn-container">
            <a href="dashboard.php" class="btn">回到會員首頁</a>
        </div>
    </div>

    <script>
    window.addEventListener("load", function () {
        var repairLabels = <?php echo json_encode(array_keys($item_costs)); ?>;
        var repairCosts = <?php echo json_encode(array_values($item_costs)); ?>;

        new Chart(document.getElementById("repairChart"), {
            type: "pie",
            data: {
                labels: repairLabels,
                datasets: [{
                    data: repairCosts,
                    backgroundColor: ["#ff6384", "#36a2eb", "#ffce56", "#4bc0c0", "#9966ff"],
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: "bottom" },
                    tooltip: { enabled: true }
                }
            }
        });
    });
    </script>

</body>
</html>
