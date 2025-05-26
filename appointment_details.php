<?php
// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制

// 檢查用戶是否已登入
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ========== 參數驗證 ==========
// 檢查是否有提供預約 ID
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$appointment_id = intval($_GET['id']);

// ========== 資料庫連線設定 ==========
$servername = "localhost";
$username = "root";
$password = "karry,roy,jackson";
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 預約資料查詢 ==========
$sql = "SELECT 
            a.*, 
            u.full_name, 
            u.contact_number,
            v.brand,
            v.model,
            v.year,
            v.license_plate
        FROM appointments a
        JOIN users u ON a.customer_id = u.user_id
        JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        WHERE a.appointment_id = ? AND a.customer_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: dashboard.php');
    exit();
}

$appointment = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>預約詳情 - 維修查詢系統</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入 FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .detail-card {
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 1rem;
            padding: 0.5em 1em;
        }
    </style>
</head>
<body>
    <!-- 導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">維修查詢系統</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">返回首頁</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主要內容 -->
    <div class="container my-5">
        <div class="card detail-card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">預約詳情 #<?= htmlspecialchars($appointment_id) ?></h3>
            </div>
            <div class="card-body">
                <!-- 狀態顯示 -->
                <div class="mb-4 text-center">
                    <?php
                    $status_class = match($appointment['status']) {
                        'pending' => 'bg-warning',
                        'confirmed' => 'bg-success',
                        'repair' => 'bg-primary',
                        'completed' => 'bg-success',
                        'cancelled' => 'bg-danger',
                        'noshow' => 'bg-dark',
                        default => 'bg-secondary'
                    };
                    $status_text = match($appointment['status']) {
                        'pending' => '待確認',
                        'confirmed' => '已確認',
                        'repair' => '維修中',
                        'completed' => '已完成',
                        'cancelled' => '已取消',
                        'noshow' => '未到',
                        default => '未知狀態'
                    };
                    ?>
                    <span class="badge <?= $status_class ?> status-badge">
                        <?= $status_text ?>
                    </span>
                </div>

                <!-- 預約資訊 -->
                <div class="row">
                    <!-- 客戶資訊 -->
                    <div class="col-md-6 mb-4">
                        <h4><i class="fas fa-user me-2"></i>客戶資訊</h4>
                        <hr>
                        <p><strong>姓名：</strong><?= htmlspecialchars($appointment['full_name']) ?></p>
                        <p><strong>聯絡電話：</strong><?= htmlspecialchars($appointment['contact_number']) ?></p>
                    </div>

                    <!-- 車輛資訊 -->
                    <div class="col-md-6 mb-4">
                        <h4><i class="fas fa-car me-2"></i>車輛資訊</h4>
                        <hr>
                        <p><strong>車牌號碼：</strong><?= htmlspecialchars($appointment['license_plate']) ?></p>
                        <p><strong>品牌：</strong><?= htmlspecialchars($appointment['brand']) ?></p>
                        <p><strong>型號：</strong><?= htmlspecialchars($appointment['model']) ?></p>
                        <p><strong>年份：</strong><?= htmlspecialchars($appointment['year']) ?></p>
                    </div>

                    <!-- 預約時間 -->
                    <div class="col-md-6 mb-4">
                        <h4><i class="fas fa-calendar me-2"></i>預約時間</h4>
                        <hr>
                        <p><strong>日期：</strong><?= htmlspecialchars($appointment['appointment_date']) ?></p>
                        <p><strong>時間：</strong><?= htmlspecialchars($appointment['appointment_time']) ?></p>
                    </div>

                    <!-- 維修項目 -->
                    <div class="col-md-6 mb-4">
                        <h4><i class="fas fa-tools me-2"></i>維修項目</h4>
                        <hr>
                        <?php
                        $service_items_map = [
                            'maintenance' => '一般維修',
                            'inspection' => '年度檢查',
                            'cleaning' => '車輛清潔',
                            'brake_replace' => '煞車更換',
                            'other' => '其他'
                        ];
                        $service_items_array = explode(',', $appointment['service_items']);
                        foreach ($service_items_array as $item) {
                            $item = trim($item);
                            $item_text = isset($service_items_map[$item]) ? $service_items_map[$item] : $item;
                            echo "<p>$item_text</p>";
                        }
                        ?>
                    </div>
                </div>

                <!-- 操作按鈕 -->
                <div class="text-center mt-4">
                    <a href="dashboard.php" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>返回儀表板
                    </a>
                    <?php if ($appointment['status'] === 'pending' || $appointment['status'] === 'confirmed'): ?>
                        <button class="btn btn-danger" onclick="cancelAppointment(<?= $appointment_id ?>)">
                            <i class="fas fa-times me-2"></i>取消預約
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cancelAppointment(appointmentId) {
            if (confirm('確定要取消該預約嗎？')) {
                fetch('api/updateAppointmentStatus.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        appointment_id: appointmentId,
                        status: 'cancelled'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('預約已成功取消！');
                        window.location.reload();
                    } else {
                        alert('取消失敗：' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('系統錯誤，請稍後再試');
                });
            }
        }
    </script>

    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
