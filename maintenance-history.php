<?php
// ========== 使用者驗證與初始化 ==========
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$userName = $_SESSION['full_name'] ?? "會員名稱";

// ========== 資料庫連線 ==========
$conn = new mysqli("localhost", "root", "", "睿煬企業社");
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 查詢登入時間 ==========
$lastLogin = date("Y-m-d H:i:s");
$stmt = $conn->prepare("SELECT last_login FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $lastLogin = date("Y-m-d H:i:s", strtotime($row['last_login']));
}
$stmt->close();

// ========== 維修紀錄查詢（整合進主畫面） ==========
$records_per_page = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $records_per_page;

$search_condition = "WHERE v.owner_id = ?";
$params = [$user_id];
$param_types = "i";

if (isset($_GET['search'])) {
    $search_terms = [];
    if (!empty($_GET['plate_number'])) {
        $search_terms[] = "v.license_plate LIKE ?";
        $params[] = "%" . $_GET['plate_number'] . "%";
        $param_types .= "s";
    }
    if (!empty($_GET['date_from'])) {
        $search_terms[] = "mr.repair_date >= ?";
        $params[] = $_GET['date_from'];
        $param_types .= "s";
    }
    if (!empty($_GET['date_to'])) {
        $search_terms[] = "mr.repair_date <= ?";
        $params[] = $_GET['date_to'];
        $param_types .= "s";
    }
    if (!empty($search_terms)) {
        $search_condition .= " AND " . implode(" AND ", $search_terms);
    }
}

$count_sql = "SELECT COUNT(*) AS total FROM maintenance_records mr JOIN vehicles v ON mr.vehicle_id = v.vehicle_id $search_condition";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);
$stmt->close();

$sql = "SELECT mr.record_id, v.vehicle_id, v.license_plate, v.model, u.full_name AS owner,
               mr.repair_date, mr.mileage, mr.recommendations, mr.customer_signature,
               mr.total_cost, mr.created_at
        FROM maintenance_records mr
        JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
        JOIN users u ON v.owner_id = u.user_id
        $search_condition
        ORDER BY mr.repair_date DESC
        LIMIT ? OFFSET ?";
$params[] = $records_per_page;
$params[] = $offset;
$param_types .= "ii";
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- HTML 結構採用會員主頁統一風格，請用 UI 元素統整 -->
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>維修紀錄 - 維修查詢系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .dashboard-card { background-color: #fff; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05); transition: 0.3s; }
        .dashboard-card:hover { transform: translateY(-5px); }
        .status-badge { font-size: 0.9rem; padding: 0.5em 1em; border-radius: 12px; color: #fff; }
        h2, h3 { font-weight: bold; border-bottom: 2px solid #f1c40f; padding-bottom: 6px; margin-bottom: 20px; }
        .table th, .table td { vertical-align: middle; white-space: nowrap; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #34495e;">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">維修查詢系統</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="profile.php">會員中心</a></li>
                <li class="nav-item"><a class="nav-link" href="appointments.php">預約服務</a></li>
                <li class="nav-item"><a class="nav-link" href="my-vehicles.php">我的車輛</a></li>
                <li class="nav-item"><a class="nav-link active" href="maintenance-history.php">維修紀錄</a></li>
                <li class="nav-item"><a class="nav-link" href="report.php">維修報表</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($userName) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">個人資料</a></li>
                        <li><a class="dropdown-item" href="change-password.php">修改密碼</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">登出</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <h3 class="mb-4">維修紀錄查詢</h3>
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">車牌號碼</label>
            <input type="text" class="form-control" name="plate_number" value="<?= $_GET['plate_number'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">日期從</label>
            <input type="date" class="form-control" name="date_from" value="<?= $_GET['date_from'] ?? '' ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">日期至</label>
            <input type="date" class="form-control" name="date_to" value="<?= $_GET['date_to'] ?? '' ?>">
        </div>
        <div class="col-12">
            <button type="submit" name="search" class="btn btn-primary">搜尋</button>
            <a href="maintenance-history.php" class="btn btn-secondary">重置</a>
        </div>
    </form>

    <table class="table table-hover table-striped">
        <thead>
        <tr>
            <th>維修編號</th>
            <th>車牌號碼</th>
            <th>型號</th>
            <th>車主</th>
            <th>維修日期</th>
            <th>公里數</th>
            <th>建議事項</th>
            <th>總金額</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['record_id']) ?></td>
                <td><?= htmlspecialchars($row['license_plate']) ?></td>
                <td><?= htmlspecialchars($row['model']) ?></td>
                <td><?= htmlspecialchars($row['owner']) ?></td>
                <td><?= htmlspecialchars($row['repair_date']) ?></td>
                <td><?= htmlspecialchars($row['mileage']) ?></td>
                <td><?= htmlspecialchars($row['recommendations']) ?></td>
                <td>$<?= number_format($row['total_cost'], 2) ?></td>
                <td>
                    <a href="maintenance_details.php?id=<?= $row['record_id'] ?>" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> 查看
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>

<footer class="bg-primary text-white text-center py-3">
    <p>&copy; 2024-2025 維修查詢系統 | 協作單位：睿煬企業社、康寧大學資管科</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
