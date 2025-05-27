<?php
// ========== 檔案說明 ==========
// maintenance_records.php 員工系統 - 維修紀錄查詢介面

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName     = "睿煬企業社";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sqlUser = "SELECT full_name FROM users WHERE user_id = '$user_id' LIMIT 1";
$resultUser = $conn->query($sqlUser);
$userName = "會員名稱";
if ($resultUser && $resultUser->num_rows > 0) {
    $row = $resultUser->fetch_assoc();
    $userName = $row['full_name'];
}

$searchPlate = isset($_GET['plate_number']) ? $conn->real_escape_string($_GET['plate_number']) : '';
$searchDate  = isset($_GET['repair_date']) ? $conn->real_escape_string($_GET['repair_date']) : '';

$sql = "SELECT mr.record_id, v.license_plate, v.model AS car_model, u.full_name AS owner,
               mr.repair_date, mr.total_cost
        FROM maintenance_records mr
        JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
        JOIN users u ON v.owner_id = u.user_id
        WHERE 1=1";

if (!empty($searchPlate)) {
    $sql .= " AND v.license_plate LIKE '%$searchPlate%'";
}
if (!empty($searchDate)) {
    $sql .= " AND mr.repair_date = '$searchDate'";
}
$sql .= " ORDER BY mr.repair_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>維修紀錄查詢 - 維修查詢系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #2c3e50;
            font-size: 18px;
        }
        h2 {
            color: #2c3e50;
            font-weight: bold;
            border-bottom: 2px solid #f1c40f;
            padding-bottom: 5px;
            margin-bottom: 20px;
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
        }
        .btn:hover {
            background-color: #f1c40f;
            color: #2c3e50;
        }
        table {
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #ecf0f1;
            color: #2c3e50;
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
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">
            <i class="fas fa-tools me-2"></i>員工系統
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="staff_dashboard.php">首頁</a></li>
                <li class="nav-item"><a class="nav-link active" href="maintenance_records.php">維修紀錄查詢</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h2 class="text-center">維修紀錄查詢</h2>
    <form method="GET" class="row g-3">
        <div class="col-md-5">
            <label for="plate_number" class="form-label">車牌號碼</label>
            <input type="text" class="form-control" name="plate_number" value="<?= htmlspecialchars($searchPlate) ?>">
        </div>
        <div class="col-md-5">
            <label for="repair_date" class="form-label">維修日期</label>
            <input type="date" class="form-control" name="repair_date" value="<?= htmlspecialchars($searchDate) ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">查詢</button>
        </div>
        <div class="col-12 text-center">
            <a href="admin_record.php" class="btn btn-success">新增維修紀錄</a>
        </div>
    </form>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>維修單號</th>
                <th>車牌號碼</th>
                <th>車型</th>
                <th>車主</th>
                <th>維修日期</th>
                <th>總金額</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if (!$result) {
            echo "<tr><td colspan='7' class='text-danger text-center'>❌ 資料查詢失敗：" . $conn->error . "</td></tr>";
        } elseif ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['record_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['license_plate']) . "</td>";
                echo "<td>" . htmlspecialchars($row['car_model']) . "</td>";
                echo "<td>" . htmlspecialchars($row['owner']) . "</td>";
                echo "<td>" . htmlspecialchars($row['repair_date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['total_cost']) . "</td>";
                echo "<td>
                        <a href='maintenance_details.php?id=" . $row['record_id'] . "' class='btn btn-sm btn-primary'>查看</a>
                        <a href='edit_maintenance.php?id=" . $row['record_id'] . "' class='btn btn-sm btn-warning'>更正</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='text-center'>查無資料</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>