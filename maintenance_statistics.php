<?php
// 資料庫連線設定
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

// 建立連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("連線失敗：" . $conn->connect_error);
}

// 取得本月的起始與結束日期
$current_month = date('Y-m'); // 例：2025-05
$start_date = $current_month . "-01";              // 當月1號
$end_date = date("Y-m-t", strtotime($start_date)); // 當月最後一天

// -----------------------------------------------
// ✅ 1. 查詢本月未完成預約筆數（pending 狀態）
// -----------------------------------------------
$sql1 = "SELECT COUNT(*) AS pending_count 
         FROM appointments 
         WHERE appointment_date BETWEEN ? AND ? AND status = 'pending'";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("ss", $start_date, $end_date);
$stmt1->execute();
$result1 = $stmt1->get_result();
$row1 = $result1->fetch_assoc();
$pending_count = is_array($row1) && isset($row1['pending_count']) ? $row1['pending_count'] : 0;

// -----------------------------------------------
// ✅ 2. 查詢本月完成預約筆數（completed 狀態）
// -----------------------------------------------
$sql2 = "SELECT COUNT(*) AS completed_count 
         FROM appointments 
         WHERE appointment_date BETWEEN ? AND ? AND status = 'completed'";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("ss", $start_date, $end_date);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
$completed_count = is_array($row2) && isset($row2['completed_count']) ? $row2['completed_count'] : 0;

// -----------------------------------------------
// ✅ 3. 查詢本月總維修收入（來自 maintenance_records）
// -----------------------------------------------
$sql3 = "SELECT SUM(total_cost) AS total_revenue 
         FROM maintenance_records 
         WHERE repair_date BETWEEN ? AND ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("ss", $start_date, $end_date);
$stmt3->execute();
$result3 = $stmt3->get_result();
$row3 = $result3->fetch_assoc();
$total_revenue = is_array($row3) && isset($row3['total_revenue']) ? $row3['total_revenue'] : 0.00;

// -----------------------------------------------
// ✅ 4. 查詢本月最常維修的車型
// -----------------------------------------------
$sql4 = "SELECT v.model, COUNT(*) AS count
         FROM maintenance_records m
         JOIN vehicles v ON m.vehicle_id = v.vehicle_id
         WHERE m.repair_date BETWEEN ? AND ?
         GROUP BY v.model
         ORDER BY count DESC
         LIMIT 1";
$stmt4 = $conn->prepare($sql4);
$stmt4->bind_param("ss", $start_date, $end_date);
$stmt4->execute();
$result4 = $stmt4->get_result();
$row4 = $result4->fetch_assoc();
$popular_model = is_array($row4) && isset($row4['model']) ? $row4['model'] : '無資料';

// -----------------------------------------------
// ✅ 顯示統計結果（HTML 區塊）
// -----------------------------------------------
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>維修統計報表</title>
    <style>
        body { font-family: "微軟正黑體", sans-serif; margin: 30px; }
        h2, h3 { color: #2c3e50; }
        .card {
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 2px 2px 6px rgba(0,0,0,0.1);
        }
        .value {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
        }
    </style>
</head>
<body>

    <h2>本月維修統計報表</h2>

    <div class="card">
        <h3>📌 本月未完成維修預約</h3>
        <p class="value"><?= $pending_count ?> 筆</p>
    </div>

    <div class="card">
        <h3>✅ 本月完成維修預約</h3>
        <p class="value"><?= $completed_count ?> 筆</p>
    </div>

    <div class="card">
        <h3>💰 本月總收入</h3>
        <p class="value">$<?= number_format($total_revenue, 2) ?> 元</p>
    </div>

    <div class="card">
        <h3>🚗 最常維修車型</h3>
        <p class="value"><?= htmlspecialchars($popular_model) ?></p>
    </div>
    <div style="text-align: center; margin-top: 30px;">
    <button onclick="history.back()" style="padding: 10px 20px; font-size: 16px; border: none; background-color: #3498db; color: white; border-radius: 5px; cursor: pointer;">
        ⬅ 返回
    </button>
</div>
</body>
</html>

<?php
// 關閉資料庫連線
$conn->close();
?>