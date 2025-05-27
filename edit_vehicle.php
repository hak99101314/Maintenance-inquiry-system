<?php
// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制

// 檢查使用者是否已登入
if (!isset($_SESSION['user_id'])) {
    die("<p class='text-danger'>請先登入。</p>");
}

// ========== 資料庫連線設定 ==========
$conn = new mysqli("localhost", "root", "", "睿煬企業社");
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// ========== 車輛 ID 驗證 ==========
$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($vehicle_id == 0) {
    die("<p class='text-danger'>錯誤：無效的車輛 ID。</p>");
}

// ========== 車輛資料查詢 ==========
$sql = "SELECT brand, model, license_plate FROM vehicles WHERE vehicle_id = $vehicle_id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    die("<p class='text-danger'>錯誤：找不到車輛。</p>");
}
$vehicle = $result->fetch_assoc();

// ========== 更新車輛資料處理 ==========
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $license_plate = $_POST['license_plate'];

    $update_sql = "UPDATE vehicles SET brand='$brand', model='$model', license_plate='$license_plate' WHERE vehicle_id=$vehicle_id";

    if ($conn->query($update_sql) === TRUE) {
        echo "<div class='alert alert-success text-center'>更新成功！2秒後返回車輛清單...</div>";
        header("Refresh:2; url=admin_vehicles.php");
    } else {
        echo "<div class='alert alert-danger'>更新失敗：" . $conn->error . "</div>";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>編輯車輛資料</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <!-- 表單卡片 -->
    <div class="card mx-auto shadow" style="max-width: 600px;">
      <div class="card-header bg-primary text-white text-center">
        <h4 class="mb-0">編輯車輛資料</h4>
      </div>
      <div class="card-body">
        <form method="POST">
          <!-- 品牌 -->
          <div class="mb-3">
            <label for="brand" class="form-label">品牌：</label>
            <input type="text" id="brand" name="brand" class="form-control" value="<?= htmlspecialchars($vehicle['brand']) ?>" required>
          </div>
          <!-- 型號 -->
          <div class="mb-3">
            <label for="model" class="form-label">型號：</label>
            <input type="text" id="model" name="model" class="form-control" value="<?= htmlspecialchars($vehicle['model']) ?>" required>
          </div>
          <!-- 車牌 -->
          <div class="mb-3">
            <label for="license_plate" class="form-label">車牌號碼：</label>
            <input type="text" id="license_plate" name="license_plate" class="form-control" value="<?= htmlspecialchars($vehicle['license_plate']) ?>" required>
          </div>
          <!-- 提交按鈕 -->
          <div class="text-center">
            <button type="submit" class="btn btn-success px-4">更新資料</button>
            <a href="admin_vehicles.php" class="btn btn-secondary ms-2">返回清單</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
