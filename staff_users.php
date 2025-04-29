<?php
// staff_users.php
// 啟用 Session 用於管理使用者登入狀態
session_start();

// 檢查使用者是否已登入，且角色為 "staff"
// 若未登入或角色不符，則導向登入頁面
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: login.php");
    exit();
}

// 資料庫連線設定
$servername = "localhost";               // 資料庫伺服器位置
$dbUsername = "root";                    // 資料庫使用者名稱
$dbPassword = "karry,roy,jackson";        // 資料庫密碼
$dbName = "睿煬企業社";                   // 資料庫名稱

// 建立新的 MySQLi 連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);

// 檢查連線是否成功
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// 執行 SQL 查詢，取得角色為 "customer" 的用戶資料
// 同時也查詢 email 與 contact_number，供後續詳細資訊使用
$sql = "SELECT user_id, username, full_name, email, contact_number FROM users WHERE role = 'customer'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客戶管理 - 員工系統</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入自訂管理後台的 CSS -->
    <link rel="stylesheet" href="assets/css/admin_styles.css">
    <style>
        body {
  background-color: #f5f6fa;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  font-size: 17px;
  color: #2c3e50;
}
h1, h2, h3 {
  font-weight: bold;
  color: #2c3e50;
  border-bottom: 2px solid #f1c40f;
  padding-bottom: 8px;
  margin-bottom: 25px;
}
.navbar {
  background-color: #2c3e50;
}
.navbar-brand, .nav-link {
  color: #ffffff !important;
  font-weight: bold;
}
.nav-link:hover {
  color: #f1c40f !important;
}
.container {
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 0 15px rgba(0,0,0,0.05);
  padding: 30px;
  margin-top: 50px;
}
.table {
  border-collapse: collapse;
}
.table th, .table td {
  padding: 15px;
  font-size: 17px;
}
.table th {
  background-color: #ecf0f1;
  color: #2c3e50;
}
.btn-outline-primary {
  border: 2px solid #2c3e50;
  color: #2c3e50;
}
.btn-outline-primary:hover {
  background-color: #f1c40f;
  border-color: #f1c40f;
  color: #2c3e50;
  font-weight: bold;
}
.modal-title {
  font-weight: bold;
}
footer {
  background-color: #2c3e50;
  color: #ffffff;
}

    </style>
</head>
<body>
    <!-- 導覽列區塊 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 系統標題 -->
            <a class="navbar-brand" href="staff_dashboard.php"><i class="fas fa-user-cog me-2"></i>員工系統</a>

            <!-- 導覽列切換按鈕（適用於行動裝置） -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- 導覽連結 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- 首頁連結 -->
                    <li class="nav-item"><a class="nav-link" href="staff_dashboard.php">首頁</a></li>
                    <!-- 客戶管理（目前頁面 active） -->
                    <li class="nav-item"><a class="nav-link active" href="staff_users.php">客戶管理</a></li>
                    <!-- 維修管理連結 -->
                    <li class="nav-item"><a class="nav-link" href="staff_orders.php">維修管理</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 客戶管理主要內容 -->
    <div class="container mt-4">
        <h2 class="text-center mb-4">客戶管理</h2>
        <!-- 使用 Bootstrap 的 table 組件顯示客戶資料 -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>客戶ID</th>
                    <th>帳號</th>
                    <th>姓名</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 若有資料則逐筆顯示
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        // 輸出客戶ID（使用 htmlspecialchars 防止 XSS）
                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                        // 輸出帳號
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        // 輸出姓名
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        // 操作按鈕：點擊後呼叫 viewCustomerDetails() 並傳入該客戶ID
                        echo "<td>
                                <button class='btn btn-sm btn-outline-primary' onclick=\"viewCustomerDetails(" . htmlspecialchars($row['user_id']) . ")\">查看詳情</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    // 若無資料則顯示提示訊息
                    echo "<tr><td colspan='4' class='text-center'>目前無客戶資料</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- 客戶詳情模態框 (Modal) -->
    <div class="modal fade" id="customerDetailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <!-- 模態框標題 -->
                <div class="modal-header">
                    <h5 class="modal-title">客戶詳細資料</h5>
                    <!-- 模態框關閉按鈕 -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- 模態框內容區，將透過 AJAX 載入客戶詳細資料 -->
                <div class="modal-body">
                    <div id="customer-details-content">
                        <!-- 客戶詳細資料內容將在此顯示 -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 頁尾區塊 -->
    <footer class="bg-primary text-light py-4 text-center">
        <p>&copy; 2024-2025 維修查詢系統 | 協作單位：睿煬企業社、康寧大學資管科17.林宸皓13.陳彥丞19.陳宗偉</p>
    </footer>

    <!-- 引入 Bootstrap JS (包含 Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript 區：實作「查看詳情」功能 -->
    <script>
    function viewCustomerDetails(userId) {
        // 使用 fetch API 向後端取得該用戶詳細資料
        fetch('get_customer_details.php?user_id=' + encodeURIComponent(userId))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 若成功取得資料，則將詳細資料填入模態框內容區
                    const details = data.data;
                    let html = '<p><strong>用戶ID：</strong>' + details.user_id + '</p>';
                    html += '<p><strong>帳號：</strong>' + details.username + '</p>';
                    html += '<p><strong>姓名：</strong>' + details.full_name + '</p>';
                    html += '<p><strong>電子郵件：</strong>' + details.email + '</p>';
                    html += '<p><strong>聯絡電話：</strong>' + details.contact_number + '</p>';
                    document.getElementById('customer-details-content').innerHTML = html;
                } else {
                    // 若查無資料，則顯示提示訊息
                    document.getElementById('customer-details-content').innerHTML = '<p>查無詳細資料</p>';
                }
                // 建立並顯示 Bootstrap 模态框
                var modal = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
                modal.show();
            })
            .catch(error => {
                // 若發生錯誤，則顯示錯誤提示
                document.getElementById('customer-details-content').innerHTML = '<p>載入資料時發生錯誤</p>';
                var modal = new bootstrap.Modal(document.getElementById('customerDetailsModal'));
                modal.show();
            });
    }
    </script>
</body>
</html>
<?php
// 關閉資料庫連線，釋放資源
$conn->close();
?>
