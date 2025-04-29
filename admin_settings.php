<?php
// ========== 資料庫連接設定 ==========
// 設定資料庫連接參數
$servername = "localhost";    // 資料庫伺服器位置
$username = "root";          // 資料庫使用者名稱
$password = 'karry,roy,jackson';              // 資料庫密碼
$dbname = "睿煬企業社";      // 資料庫名稱

// 建立資料庫連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接是否成功
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);  // 如果連接失敗，顯示錯誤訊息並終止程式
}

// ========== 資料表檢查和創建 ==========
// 檢查 system_settings 資料表是否存在
$table_check_sql = "SHOW TABLES LIKE 'system_settings'";
$table_check_result = $conn->query($table_check_sql);

// 如果資料表不存在，則創建它
if ($table_check_result->num_rows == 0) {
    // 定義創建資料表的 SQL 語句
    $create_sql = "CREATE TABLE `system_settings` (
        `setting_name` VARCHAR(255) NOT NULL COMMENT '設定名稱',
        `value` TEXT DEFAULT NULL COMMENT '設定值',
        PRIMARY KEY (`setting_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='系統設定表'";
    
    // 執行創建資料表的操作
    if ($conn->query($create_sql) === TRUE) {
        echo "<div class='alert alert-success'>系統設定表已成功創建！</div>";
    } else {
        echo "<div class='alert alert-danger'>錯誤：無法創建系統設定表 - " . $conn->error . "</div>";
        exit();
    }
}

// ========== 處理表單提交 ==========
// 檢查是否有 POST 請求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 更新公司名稱設定
    if (isset($_POST['company_name'])) {
        $company_name = $_POST['company_name'];
        // 使用 INSERT ... ON DUPLICATE KEY UPDATE 來新增或更新設定
        $sql = "INSERT INTO system_settings (setting_name, value) VALUES ('company_name', ?) ON DUPLICATE KEY UPDATE value = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $company_name, $company_name);
        $stmt->execute();
    }
    
    // 更新聯絡信箱設定
    if (isset($_POST['contact_email'])) {
        $contact_email = $_POST['contact_email'];
        $sql = "INSERT INTO system_settings (setting_name, value) VALUES ('contact_email', ?) ON DUPLICATE KEY UPDATE value = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $contact_email, $contact_email);
        $stmt->execute();
    }
    
    // 更新聯絡電話設定
    if (isset($_POST['contact_phone'])) {
        $contact_phone = $_POST['contact_phone'];
        $sql = "INSERT INTO system_settings (setting_name, value) VALUES ('contact_phone', ?) ON DUPLICATE KEY UPDATE value = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $contact_phone, $contact_phone);
        $stmt->execute();
    }
    
    // 重新導向到設定頁面並顯示成功訊息
    header("Location: admin_settings.php?success=1");
    exit();
}

// ========== 獲取當前設定 ==========
// 從資料庫中讀取所有系統設定
$sql = "SELECT * FROM system_settings";
$result = $conn->query($sql);
$settings = [];
// 將設定值存入陣列中
while($row = $result->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['value'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>系統設定</title>
    <meta charset="UTF-8">
    <!-- 引入 Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <!-- 主要內容容器 -->
    <div class="container mt-5">
        <h2>系統設定</h2>
        
        <!-- 顯示成功訊息（如果有） -->
        <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            設定已成功更新！
        </div>
        <?php endif; ?>

        <!-- 如果沒有設定資料，顯示提示訊息 -->
        <?php if(empty($settings)): ?>
        <div class="alert alert-info">
            目前沒有任何設定資料。
        </div>
        <?php else: ?>
        <!-- 設定表單 -->
        <form method="POST" action="">
            <!-- 公司名稱設定 -->
            <div class="form-group">
                <label>公司名稱</label>
                <input type="text" class="form-control" name="company_name" 
                       value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>">
            </div>
            
            <!-- 聯絡信箱設定 -->
            <div class="form-group">
                <label>聯絡信箱</label>
                <input type="email" class="form-control" name="contact_email" 
                       value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>">
            </div>
            
            <!-- 聯絡電話設定 -->
            <div class="form-group">
                <label>聯絡電話</label>
                <input type="text" class="form-control" name="contact_phone" 
                       value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>">
            </div>
            
            <!-- 提交按鈕 -->
            <button type="submit" class="btn btn-primary">儲存設定</button>
        </form>
        <?php endif; ?>
    </div>

    <!-- 引入必要的 JavaScript 函式庫 -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
