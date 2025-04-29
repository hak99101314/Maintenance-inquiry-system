<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = 'karry,roy,jackson';
$dbname = "睿煬企業社";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

$maintenance_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($maintenance_id > 0) {
    $sql = "SELECT * FROM repair_orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $maintenance_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $maintenance = $result->fetch_assoc();
}

if (!$maintenance) {
    header("Location: maintenance_records.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>更正維修紀錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: #2c3e50;
        }

        .navbar-brand, .nav-link {
            color: #fff !important;
            font-weight: bold;
        }

        .nav-link:hover {
            color: #f1c40f !important;
        }

        h2 {
            color: #2c3e50;
            font-weight: bold;
            margin-bottom: 25px;
            border-bottom: 2px solid #f1c40f;
            padding-bottom: 8px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }

        .form-label {
            font-weight: bold;
            color: #34495e;
        }

        .form-control {
            border-radius: 6px;
        }

        .btn-primary, .btn-success {
            background-color: #2c3e50;
            border: none;
        }

        .btn-primary:hover, .btn-success:hover {
            background-color: #f1c40f;
            color: #2c3e50;
            font-weight: bold;
        }

        .repair-item {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .btn-danger {
            background-color: #c0392b;
            border: none;
        }

        .btn-danger:hover {
            background-color: #922b21;
        }

    </style>
</head>
<body>
    <!-- 導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'staff_dashboard.php' ?>">
                <?= $_SESSION['role'] === 'admin' ? '管理員系統' : '員工系統' ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="maintenance_records.php">返回維修紀錄</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主內容 -->
    <div class="container my-5">
        <h2 class="text-center">更正維修紀錄</h2>

        <div class="card p-4">
            <form id="editMaintenanceForm" method="post">
                <input type="hidden" name="maintenance_id" value="<?= $maintenance_id ?>">

                <!-- 基本資料 -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">車牌號碼</label>
                        <input type="text" class="form-control" name="plate_number" value="<?= htmlspecialchars($maintenance['plate_number']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">車型</label>
                        <input type="text" class="form-control" name="car_model" value="<?= htmlspecialchars($maintenance['car_model']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">車主姓名</label>
                        <input type="text" class="form-control" name="owner" value="<?= htmlspecialchars($maintenance['owner']) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">維修日期</label>
                        <input type="date" class="form-control" name="repair_date" value="<?= htmlspecialchars($maintenance['repair_date']) ?>" required>
                    </div>
                </div>

                <!-- 維修項目區塊 -->
                <h5 class="mt-4">維修項目</h5>
                <div id="repairItems"></div>
                <button type="button" class="btn btn-success mt-2" onclick="addRepairItem()">
                    <i class="fas fa-plus"></i> 新增維修項目
                </button>

                <!-- 操作按鈕 -->
                <div class="text-end mt-4">
                    <button type="button" class="btn btn-secondary me-2" onclick="history.back()">取消</button>
                    <button type="submit" class="btn btn-primary">儲存更改</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function addRepairItem(item = null) {
        const itemHtml = `
            <div class="repair-item p-3 mb-3">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">維修項目</label>
                        <input type="text" class="form-control" name="items[]" value="${item?.item_name || ''}" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">數量</label>
                        <input type="number" class="form-control" name="quantities[]" value="${item?.quantity || 1}" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">單價</label>
                        <input type="number" class="form-control" name="prices[]" value="${item?.price || 0}" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger w-100" onclick="this.closest('.repair-item').remove()">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>`;
        document.getElementById('repairItems').insertAdjacentHTML('beforeend', itemHtml);
    }

    // 初始新增一個空欄位
    addRepairItem();

    document.getElementById('editMaintenanceForm').onsubmit = function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = {
            maintenance_id: formData.get('maintenance_id'),
            plate_number: formData.get('plate_number'),
            car_model: formData.get('car_model'),
            owner: formData.get('owner'),
            repair_date: formData.get('repair_date'),
            repair_items: []
        };

        const items = formData.getAll('items[]');
        const quantities = formData.getAll('quantities[]');
        const prices = formData.getAll('prices[]');

        for (let i = 0; i < items.length; i++) {
            data.repair_items.push({
                item_name: items[i],
                quantity: quantities[i],
                price: prices[i]
            });
        }

        fetch('api/updateMaintenance.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert('更新成功！');
                window.location.href = 'maintenance_records.php';
            } else {
                alert('更新失敗：' + result.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('系統錯誤，請稍後再試');
        });
    };
    </script>
</body>
</html>
<?php $conn->close(); ?>
