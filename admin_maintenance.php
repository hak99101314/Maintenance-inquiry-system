<?php
session_start();

// ========== 權限檢查（僅限管理員） ==========
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ========== 資料庫連線 ==========
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName     = "睿煬企業社";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 查詢條件處理 ==========
$searchPlate = isset($_GET['plate_number']) ? $conn->real_escape_string($_GET['plate_number']) : '';
$searchDate  = isset($_GET['repair_date']) ? $conn->real_escape_string($_GET['repair_date']) : '';

// ========== 主查詢語句：從維修紀錄 + 車輛 + 車主資訊 ==========
$sql = "SELECT 
            mr.record_id AS id,
            v.license_plate AS plate_number,
            v.model AS car_model,
            u.full_name AS owner,
            mr.repair_date,
            mr.total_cost
        FROM maintenance_records mr
        LEFT JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
        LEFT JOIN users u ON v.owner_id = u.user_id
        WHERE 1=1";

// 篩選條件
if (!empty($searchPlate)) {
    $sql .= " AND v.license_plate LIKE '%" . $searchPlate . "%'";
}
if (!empty($searchDate)) {
    $sql .= " AND mr.repair_date = '" . $searchDate . "'";
}
$sql .= " ORDER BY mr.repair_date DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>維修紀錄管理 - 管理員系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #2c3e50;
            font-size: 18px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-top: 50px;
        }
        .btn {
            background-color: #2c3e50;
            color: #fff;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #f1c40f;
            color: #2c3e50;
            font-weight: bold;
        }
        .navbar {
            background-color: #2c3e50;
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
            font-weight: bold;
        }
        .nav-link.active {
            color: #f1c40f !important;
        }
    </style>
</head>
<body>
    <!-- ========== 導覽列 ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-tools me-2"></i>管理員系統
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_maintenance.php">維修紀錄管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== 主內容區塊 ========== -->
    <div class="container">
        <h2 class="text-center">維修紀錄管理</h2>
        <!-- 查詢表單 -->
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <label for="plate_number" class="form-label">車牌號碼</label>
                <input type="text" class="form-control" id="plate_number" name="plate_number"
                       placeholder="輸入完整或部分車牌號碼" value="<?= htmlspecialchars($searchPlate) ?>">
            </div>
            <div class="col-md-5">
                <label for="repair_date" class="form-label">維修日期</label>
                <input type="date" class="form-control" id="repair_date" name="repair_date"
                       value="<?= htmlspecialchars($searchDate) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn w-100">查詢</button>
            </div>
            <div class="col-12 text-center mt-3">
                <a href="admin_record.php" class="btn btn-success">新增維修紀錄</a>
            </div>
        </form>

        <!-- 結果表格 -->
        <table class="table table-striped table-bordered mt-3">
            <thead>
                <tr>
                    <th>維修單號</th>
                    <th>車牌號碼</th>
                    <th>車型</th>
                    <th>車主</th>
                    <th>維修日期</th>
                    <th>總計金額</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['plate_number']) ?></td>
                            <td><?= htmlspecialchars($row['car_model']) ?></td>
                            <td><?= htmlspecialchars($row['owner']) ?></td>
                            <td><?= htmlspecialchars($row['repair_date']) ?></td>
                            <td><?= htmlspecialchars($row['total_cost']) ?></td>
                            <td>
                                <a href="maintenance_details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">查看</a>
                                <a href="edit_maintenance.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">更正</a>
                                <button class="btn btn-sm btn-danger" onclick="deleteRepairOrder(<?= $row['id'] ?>)">刪除</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">查無資料</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ========== 刪除確認與 AJAX ==========
         TODO: 你必須建立 delete_maintenance.php 來接收 POST 請求並執行刪除 -->
    <script>
    function deleteRepairOrder(repairId) {
        if (!confirm("確定要刪除此維修單嗎？")) return;

        fetch("delete_maintenance.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ repair_id: repairId })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.status === "success") {
                location.reload();
            }
        })
        .catch(error => console.error("刪除錯誤:", error));
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
