<?php
session_start();
require 'db_connect.php'; // 確保這個檔案存在

// 檢查是否登入
if (!isset($_SESSION["user_id"])) {
    echo "<script>alert('請先登入'); window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION["user_id"];

// 查詢會員的維修單
$sql = "SELECT ro.id, ro.plate_number, ro.car_model, ro.repair_date, ro.total_cost 
        FROM repair_orders ro
        JOIN users u ON ro.owner = u.full_name
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 查詢所有維修項目
$sql = "SELECT ri.order_id, ri.repair_item, ri.quantity, ri.unit_price, ri.subtotal 
        FROM repair_items ri
        JOIN repair_orders ro ON ri.order_id = ro.id
        JOIN users u ON ro.owner = u.full_name
        WHERE u.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// 整理維修項目資料以供圓餅圖使用
$item_costs = [];
foreach ($items as $item) {
    if (!isset($item_costs[$item['repair_item']])) {
        $item_costs[$item['repair_item']] = 0;
    }
    $item_costs[$item['repair_item']] += $item['subtotal'];
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
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background-color: #f8f9fa; 
            color: #333;
        }
        .container { 
            max-width: 900px; 
            margin: auto; 
            background: white; 
            padding: 20px; 
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1); 
            border-radius: 8px;
        }
        h2, h3 { text-align: center; color: #007BFF; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background: white;
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 10px; 
            text-align: center; 
        }
        th { background-color: #007BFF; color: white; }
        tr:hover { background-color: #f1f1f1; }
        canvas { 
            max-width: 600px; 
            display: block; 
            margin: auto;
        }
        .btn-container { 
            text-align: center; 
            margin-top: 20px; 
        }
        .btn { 
            display: inline-block; 
            padding: 12px 20px; 
            background: #007BFF; 
            color: white; 
            text-decoration: none; 
            border-radius: 5px; 
            transition: 0.3s;
        }
        .btn:hover { background: #0056b3; }
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
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['plate_number']); ?></td>
                    <td><?php echo htmlspecialchars($order['car_model']); ?></td>
                    <td><?php echo htmlspecialchars($order['repair_date']); ?></td>
                    <td><?php echo number_format($order['total_cost'], 2); ?></td>
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
                <?php foreach ($items as $item) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($item['repair_item']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo number_format($item['unit_price'], 2); ?></td>
                    <td><?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <h3>維修費用比例</h3>
        <canvas id="repairChart"></canvas>

        <div class="btn-container">
            <a href="dashboard.php" class="btn">回到會員首頁</a>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
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
