<?php
// ========== 初始化設定 ==========
session_start();  // 啟動會話，用於處理用戶登入狀態

// ========== 登入檢查 ==========
// 確認用戶是否已登入，如果未登入則顯示錯誤訊息
if (!isset($_SESSION['user_id'])) {
    die("請先登入以查看您的維修紀錄。");
}

// 取得當前登入會員的ID
$user_id = $_SESSION['user_id'];

// ========== 資料庫連線設定 ==========
$servername = "localhost";    // 資料庫伺服器名稱
$username = "root";          // 資料庫使用者名稱
$password = 'karry,roy,jackson';              // 資料庫密碼
$dbname = "睿煬企業社";      // 資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);
// 檢查連線是否成功
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// ========== 分頁設定 ==========
$records_per_page = 10;  // 每頁顯示的記錄數
// 獲取當前頁碼，如果未指定則預設為第1頁
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
// 計算資料偏移量
$offset = ($page - 1) * $records_per_page;

// ========== 搜尋條件處理 ==========
// 初始化搜尋條件（預設只顯示當前用戶的資料）
$search_condition = "WHERE v.owner_id = ?";
$params = [$user_id];           // 參數陣列
$param_types = "i";             // 參數類型（i 表示整數）

// 處理搜尋表單提交的條件
if (isset($_GET['search'])) {
    $search_terms = [];
    
    // 處理車牌號碼搜尋
    if (!empty($_GET['plate_number'])) {
        $search_terms[] = "ro.plate_number LIKE ?";
        $params[] = "%" . $_GET['plate_number'] . "%";
        $param_types .= "s";  // s 表示字串類型
    }
    
    // 處理起始日期搜尋
    if (!empty($_GET['date_from'])) {
        $search_terms[] = "ro.repair_date >= ?";
        $params[] = $_GET['date_from'];
        $param_types .= "s";
    }
    
    // 處理結束日期搜尋
    if (!empty($_GET['date_to'])) {
        $search_terms[] = "ro.repair_date <= ?";
        $params[] = $_GET['date_to'];
        $param_types .= "s";
    }
    
    // 組合所有搜尋條件
    if (!empty($search_terms)) {
        $search_condition .= " AND " . implode(" AND ", $search_terms);
    }
}

// ========== 查詢總記錄數 ==========
// 計算符合搜尋條件的總記錄數，用於分頁
$count_sql = "SELECT COUNT(*) as total 
              FROM repair_orders ro
              JOIN vehicles v ON ro.plate_number = v.license_plate
              $search_condition";

$stmt = $conn->prepare($count_sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);  // 計算總頁數

// ========== 查詢維修記錄 ==========
// 查詢符合條件的維修記錄，並進行分頁
$sql = "SELECT ro.id, ro.plate_number, ro.car_model, ro.owner, ro.repair_date, ro.total_cost 
        FROM repair_orders ro
        JOIN vehicles v ON ro.plate_number = v.license_plate
        $search_condition
        ORDER BY ro.repair_date DESC 
        LIMIT ? OFFSET ?";

// 添加分頁參數
$params[] = $records_per_page;
$params[] = $offset;
$param_types .= "ii";

// 執行查詢
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>維修歷史查詢</title>
    <!-- 引入 Bootstrap 和 Font Awesome 的 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- ========== 導覽列 ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 網站標題 -->
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-tools me-2"></i>會員中心
            </a>
            <!-- 手機版選單按鈕 -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- 導覽列選單 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link active" href="maintenance-history.php">維修歷史</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== 主要內容區域 ========== -->
    <div class="container mt-4">
        <h2 class="text-center">維修歷史紀錄</h2>

        <!-- 搜尋表單 -->
        <div class="search-form mb-4">
            <form method="GET" class="row g-3">
                <!-- 車牌號碼搜尋欄位 -->
                <div class="col-md-3">
                    <label class="form-label">車牌號碼</label>
                    <input type="text" class="form-control" name="plate_number" 
                           value="<?= isset($_GET['plate_number']) ? htmlspecialchars($_GET['plate_number']) : '' ?>">
                </div>
                <!-- 起始日期搜尋欄位 -->
                <div class="col-md-3">
                    <label class="form-label">日期從</label>
                    <input type="date" class="form-control" name="date_from" 
                           value="<?= isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : '' ?>">
                </div>
                <!-- 結束日期搜尋欄位 -->
                <div class="col-md-3">
                    <label class="form-label">日期至</label>
                    <input type="date" class="form-control" name="date_to" 
                           value="<?= isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : '' ?>">
                </div>
                <!-- 搜尋按鈕 -->
                <div class="col-12">
                    <button type="submit" name="search" class="btn btn-primary">搜尋</button>
                    <a href="maintenance-history.php" class="btn btn-secondary">重置</a>
                </div>
            </form>
        </div>

        <!-- 維修記錄表格 -->
        <table class="table table-striped">
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
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <!-- 顯示維修記錄資料，使用 htmlspecialchars 防止 XSS 攻擊 -->
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['plate_number']) ?></td>
                    <td><?= htmlspecialchars($row['car_model']) ?></td>
                    <td><?= htmlspecialchars($row['owner']) ?></td>
                    <td><?= htmlspecialchars($row['repair_date']) ?></td>
                    <td><?= htmlspecialchars($row['total_cost']) ?></td>
                    <td>
                        <!-- 查看詳細資料按鈕 -->
                        <a href="maintenance_details.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> 查看
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- 分頁導覽 -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
