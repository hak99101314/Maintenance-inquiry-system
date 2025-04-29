<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("<p style='color:red;'>請先登入。</p>");
}
$conn = new mysqli("localhost", "root", "", "睿煬企業社");
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}
$sql = "SELECT v.vehicle_id, v.brand AS vehicle_name, v.model AS vehicle_type, 
               u.username AS owner_name, v.license_plate 
        FROM vehicles v
        JOIN users u ON v.owner_id = u.user_id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理所有車輛</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-weight: bold;
            color: #2c3e50;
            border-left: 6px solid #3498db;
            padding-left: 15px;
        }
        .table th {
            background-color: #3498db;
            color: #fff;
        }
        .action-btn {
            padding: 5px 12px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
        }
        .edit-btn { background-color: #3498db; color: white; }
        .delete-btn { background-color: #e74c3c; color: white; }
        .add-btn { background-color: #2ecc71; color: white; margin-bottom: 15px; }
        .back-btn { background-color: #95a5a6; color: white; margin-bottom: 15px; }
        .modal {
            display: none;
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .modal.show {
            display: block;
        }
        .modal input, .modal select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .modal button {
            width: 48%;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🚗 管理所有車輛</h2>

    <a href="admin_dashboard.php" class="btn back-btn">← 返回管理員首頁</a>
    <button class="btn add-btn" onclick="showModal()">＋ 新增車輛</button>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>車輛ID</th>
            <th>品牌</th>
            <th>型號</th>
            <th>車牌號碼</th>
            <th>車主姓名</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['vehicle_id']) ?></td>
            <td><?= htmlspecialchars($row['vehicle_name']) ?></td>
            <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
            <td><?= htmlspecialchars($row['license_plate']) ?></td>
            <td><?= htmlspecialchars($row['owner_name']) ?></td>
            <td>
                <a href="edit_vehicle.php?id=<?= $row['vehicle_id'] ?>" class="btn btn-sm edit-btn">編輯</a>
                <a href="delete_vehicle.php?id=<?= $row['vehicle_id'] ?>" class="btn btn-sm delete-btn" onclick="return confirm('確定要刪除這台車輛嗎？');">刪除</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <div id="addVehicleModal" class="modal">
        <h5 class="mb-3">🆕 新增車輛</h5>
        <input type="text" id="brand" placeholder="品牌">
        <input type="text" id="model" placeholder="型號">
        <input type="text" id="license_plate" placeholder="車牌號碼">
        <select id="owner_id">
            <?php
            $user_sql = "SELECT user_id, username FROM users";
            $user_result = $conn->query($user_sql);
            while ($user = $user_result->fetch_assoc()) {
                echo "<option value='{$user['user_id']}'>{$user['username']}</option>";
            }
            ?>
        </select>
        <div class="d-flex justify-content-between">
            <button class="btn btn-success" onclick="addVehicle()">儲存</button>
            <button class="btn btn-danger" onclick="hideModal()">取消</button>
        </div>
    </div>
</div>

<script>
function showModal() {
    document.getElementById('addVehicleModal').classList.add('show');
}
function hideModal() {
    document.getElementById('addVehicleModal').classList.remove('show');
}
function addVehicle() {
    const brand = document.getElementById("brand").value;
    const model = document.getElementById("model").value;
    const license_plate = document.getElementById("license_plate").value;
    const owner_id = document.getElementById("owner_id").value;

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "add_vehicle.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            alert(xhr.responseText);
            location.reload();
        }
    };
    xhr.send("brand=" + brand + "&model=" + model + "&license_plate=" + license_plate + "&owner_id=" + owner_id);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
