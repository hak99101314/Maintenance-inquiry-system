<?php
session_start();

// 僅限管理員或員工
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

// 資料庫連線
$conn = new mysqli("localhost", "root", "", "睿煬企業社");
if ($conn->connect_error) die("連線失敗：" . $conn->connect_error);

// 取得 record_id
if (!isset($_GET['id'])) die("請提供維修紀錄 ID");
$record_id = intval($_GET['id']);

// ========== 表單送出處理 ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $repair_date = $_POST['repair_date'];
    $mileage = $_POST['mileage'];
    $recommendations = $_POST['recommendations'];
    $customer_signature = $_POST['customer_signature'];
    $total_cost = $_POST['total_cost'];

    // 更新主紀錄
    $stmt = $conn->prepare("UPDATE maintenance_records SET repair_date=?, mileage=?, recommendations=?, customer_signature=?, total_cost=? WHERE record_id=?");
    $stmt->bind_param("ssssdi", $repair_date, $mileage, $recommendations, $customer_signature, $total_cost, $record_id);
    $stmt->execute();

    // 清除舊的項目明細
    $conn->query("DELETE FROM maintenance_items WHERE record_id = $record_id");

    // 重建新的項目明細
    if (!empty($_POST['item_name'])) {
        foreach ($_POST['item_name'] as $i => $name) {
            $qty = intval($_POST['quantity'][$i]);
            $price = floatval($_POST['unit_price'][$i]);
            $stmt = $conn->prepare("INSERT INTO maintenance_items (record_id, item_name, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isid", $record_id, $name, $qty, $price);
            $stmt->execute();
        }
    }

    echo "<script>alert('修改成功！');location.href='admin_maintenance.php';</script>";
    exit();
}

// ========== 初始資料查詢 ==========
$sql = "SELECT mr.*, v.license_plate, v.model, u.full_name FROM maintenance_records mr
        JOIN vehicles v ON mr.vehicle_id = v.vehicle_id
        JOIN users u ON v.owner_id = u.user_id
        WHERE mr.record_id = $record_id";
$record = $conn->query($sql)->fetch_assoc();

$sql_items = "SELECT * FROM maintenance_items WHERE record_id = $record_id";
$items = $conn->query($sql_items);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>編輯維修紀錄</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 30px; background-color: #f8f9fa; font-family: 'Segoe UI'; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4 text-center">編輯維修紀錄</h2>
    <form method="POST">
        <div class="mb-3">
            <label>車主姓名</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($record['full_name']) ?>" disabled>
        </div>
        <div class="mb-3">
            <label>車牌號碼</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($record['license_plate']) ?>" disabled>
        </div>
        <div class="mb-3">
            <label>車型</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($record['model']) ?>" disabled>
        </div>
        <div class="mb-3">
            <label for="repair_date">維修日期</label>
            <input type="date" name="repair_date" class="form-control" value="<?= $record['repair_date'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="mileage">里程數</label>
            <input type="text" name="mileage" class="form-control" value="<?= $record['mileage'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="recommendations">建議事項</label>
            <textarea name="recommendations" class="form-control" rows="3"><?= $record['recommendations'] ?></textarea>
        </div>
        <div class="mb-3">
            <label for="customer_signature">客戶簽名</label>
            <input type="text" name="customer_signature" class="form-control" value="<?= $record['customer_signature'] ?>">
        </div>

        <h5 class="mt-4">維修項目</h5>
        <table class="table table-bordered" id="items-table">
            <thead>
                <tr>
                    <th>項目名稱</th>
                    <th>數量</th>
                    <th>單價</th>
                    <th>小計</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><input type="text" name="item_name[]" class="form-control" value="<?= $item['item_name'] ?>"></td>
                        <td><input type="number" name="quantity[]" class="form-control" value="<?= $item['quantity'] ?>" onchange="updateSubtotal(this)"></td>
                        <td><input type="number" name="unit_price[]" step="0.01" class="form-control" value="<?= $item['unit_price'] ?>" onchange="updateSubtotal(this)"></td>
                        <td class="subtotal text-end">$<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
                        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">刪除</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="button" class="btn btn-secondary" onclick="addRow()">新增項目</button>

        <div class="mt-3 mb-4">
            <label>總金額：</label>
            <input type="text" name="total_cost" id="total_cost" class="form-control fw-bold text-danger" readonly>
        </div>

        <button type="submit" class="btn btn-primary">儲存變更</button>
        <a href="admin_maintenance.php" class="btn btn-secondary">取消</a>
    </form>
</div>

<script>
function addRow() {
    const table = document.querySelector("#items-table tbody");
    const row = document.createElement("tr");
    row.innerHTML = `
        <td><input type="text" name="item_name[]" class="form-control"></td>
        <td><input type="number" name="quantity[]" class="form-control" value="1" onchange="updateSubtotal(this)"></td>
        <td><input type="number" name="unit_price[]" step="0.01" class="form-control" value="0" onchange="updateSubtotal(this)"></td>
        <td class="subtotal text-end">$0.00</td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">刪除</button></td>
    `;
    table.appendChild(row);
    updateTotal();
}

function removeRow(btn) {
    btn.closest("tr").remove();
    updateTotal();
}

function updateSubtotal(input) {
    const row = input.closest("tr");
    const qty = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
    const price = parseFloat(row.querySelector('input[name="unit_price[]"]').value) || 0;
    const subtotal = qty * price;
    row.querySelector(".subtotal").textContent = "$" + subtotal.toFixed(2);
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll("#items-table tbody tr").forEach(row => {
        const qty = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name="unit_price[]"]').value) || 0;
        total += qty * price;
    });
    document.getElementById("total_cost").value = total.toFixed(2);
}

// 初始化
updateTotal();
</script>
</body>
</html>
<?php $conn->close(); ?>
