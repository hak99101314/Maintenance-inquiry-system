<?php
// ========== 檔案說明 ==========
// 檔案名稱：add_vehicle.php
// 功能說明：提供新增車輛的介面和 API 功能
// 使用對象：已登入的系統使用者

// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制

// 檢查使用者是否已登入，若未登入則導向登入頁面
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 從會話中取得使用者 ID
$user_id = $_SESSION['user_id'];

// ========== 資料庫連線設定 ==========
// 設定資料庫連線參數
$servername = "localhost";    // 資料庫伺服器位址
$username = "root";          // 資料庫使用者名稱
$password = "";              // 資料庫密碼
$dbname = "睿煬企業社";      // 資料庫名稱

// 建立資料庫連線，並檢查連線是否成功
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "資料庫連線失敗: " . $conn->connect_error]));
}

// ========== API 請求處理 ==========
// 檢查是否為 POST 請求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 解析 JSON 格式的請求內容
    $data = json_decode(file_get_contents("php://input"), true);

    // 驗證所有必要欄位是否都有值
    if (!isset($data["license_plate"], $data["brand"], $data["model"], $data["engine_number"], $data["year"], $data["month"])) {
        echo json_encode(["success" => false, "message" => "所有欄位皆為必填"]);
        exit();
    }

    // 處理並清理輸入資料，防止 SQL 注入
    $license_plate = $conn->real_escape_string($data["license_plate"]);
    $brand = $conn->real_escape_string($data["brand"]);
    $model = $conn->real_escape_string($data["model"]);
    $engine_number = $conn->real_escape_string($data["engine_number"]);
    $year = intval($data["year"]);    // 轉換為整數
    $month = intval($data["month"]);  // 轉換為整數

    // 準備 SQL 插入語句，使用參數化查詢防止 SQL 注入
    $sql = "INSERT INTO vehicles (license_plate, owner_id, brand, model, engine_number, year, month) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisssii", $license_plate, $user_id, $brand, $model, $engine_number, $year, $month);

    // 執行插入操作並回傳結果
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "車輛已成功新增"]);
    } else {
        echo json_encode(["success" => false, "message" => "新增車輛失敗: " . $stmt->error]);
    }

    // 關閉預處理語句和資料庫連線
    $stmt->close();
    $conn->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增車輛 - 維修查詢系統</title>
    <!-- 引入 Bootstrap CSS 框架 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入自訂樣式表 -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- ========== 導覽列 ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 系統標題 -->
            <a class="navbar-brand" href="dashboard.php">維修查詢系統</a>
            <!-- 響應式選單按鈕 -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- 導覽選單 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link active" href="add_vehicle.php">新增車輛</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php" onclick="logout()">登出</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== 新增車輛表單 ========== -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">新增車輛</h2>
                <!-- 表單區塊 -->
                <form id="add-vehicle-form" class="p-4 border rounded bg-light">
                    <!-- 車牌號碼輸入欄位 -->
                    <div class="mb-3">
                        <label for="licensePlate" class="form-label">車牌號碼</label>
                        <input type="text" class="form-control" id="licensePlate" placeholder="輸入車牌號碼" required>
                    </div>
                    <!-- 品牌輸入欄位 -->
                    <div class="mb-3">
                        <label for="brand" class="form-label">品牌</label>
                        <input type="text" class="form-control" id="brand" placeholder="輸入品牌" required>
                    </div>
                    <!-- 型號輸入欄位 -->
                    <div class="mb-3">
                        <label for="model" class="form-label">型號</label>
                        <input type="text" class="form-control" id="model" placeholder="輸入型號" required>
                    </div>
                    <!-- 引擎編號輸入欄位 -->
                    <div class="mb-3">
                        <label for="engineNumber" class="form-label">引擎編號</label>
                        <input type="text" class="form-control" id="engineNumber" placeholder="輸入引擎編號" required>
                    </div>
                    <!-- 年份輸入欄位 -->
                    <div class="mb-3">
                        <label for="year" class="form-label">年份</label>
                        <input type="number" class="form-control" id="year" placeholder="輸入年份 (例如: 2022)" required>
                    </div>
                    <!-- 月份輸入欄位 -->
                    <div class="mb-3">
                        <label for="month" class="form-label">月份</label>
                        <input type="number" class="form-control" id="month" placeholder="輸入月份 (1-12)" required>
                    </div>
                    <!-- 提交按鈕 -->
                    <button type="submit" class="btn btn-primary w-100">新增車輛</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ========== JavaScript 功能 ========== -->
    <script>
        // 表單提交事件處理
        document.getElementById('add-vehicle-form').addEventListener('submit', async function(e) {
            e.preventDefault();  // 防止表單預設提交行為

            // 取得表單欄位值並去除空白
            const licensePlate = document.getElementById('licensePlate').value.trim();
            const brand = document.getElementById('brand').value.trim();
            const model = document.getElementById('model').value.trim();
            const engineNumber = document.getElementById('engineNumber').value.trim();
            const year = document.getElementById('year').value.trim();
            const month = document.getElementById('month').value.trim();

            try {
                // 發送 POST 請求到 API
                const response = await fetch('add_vehicle.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        license_plate: licensePlate,
                        brand: brand,
                        model: model,
                        engine_number: engineNumber,
                        year: year,
                        month: month
                    })
                });

                // 解析回應結果
                const result = await response.json();

                // 根據結果顯示訊息並進行相應處理
                if (result.success) {
                    alert('車輛已成功新增！');
                    window.location.href = 'dashboard.php';  // 成功後導向儀表板
                } else {
                    alert(result.message || '新增車輛失敗');
                }
            } catch (error) {
                alert('伺服器錯誤，請稍後再試');
            }
        });

        // 登出確認函式
        function logout() {
            if (confirm('確定要登出嗎？')) {
                window.location.href = 'logout.php';
            }
        }
    </script>

    <!-- 引入 Bootstrap JS 框架 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
