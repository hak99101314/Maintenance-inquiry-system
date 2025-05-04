<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$dbname = "睿煬企業社";
$username = "root";
$password = 'karry,roy,jackson';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連線失敗：" . $conn->connect_error);
}

$message = "";

// 📥 表單處理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $license_plate = $_POST['license_plate'];
    $items = $_POST['items'];
    $total_price = floatval($_POST['total_price']);

    // 透過車牌找到對應的會員 ID（owner_id）
    $stmt = $conn->prepare("SELECT owner_id FROM vehicles WHERE license_plate = ?");
$stmt->bind_param("s", $license_plate);
$stmt->execute();
$stmt->bind_result($member_id);
$stmt->fetch();
$stmt->close();

// 這樣判斷就好（看 $member_id 有沒有值）
if ($member_id) {

    // 這裡才準備要插入估價單
    $insert = $conn->prepare("INSERT INTO estimates (member_id, license_plate, items, total_price, created_by) VALUES (?, ?, ?, ?, ?)");
    $insert->bind_param("issdi", $member_id, $license_plate, $items, $total_price, $_SESSION['user_id']);

    if ($insert->execute()) {
        $message = "✅ 估價單新增成功！";

        // ====== 新增後寄送通知 Email ======
        require_once 'send_email.php';

        $emailQuery = $conn->prepare("SELECT email, full_name FROM users WHERE user_id = ?");
        $emailQuery->bind_param("i", $member_id);
        $emailQuery->execute();
        $emailResult = $emailQuery->get_result();
        $emailData = $emailResult->fetch_assoc();
        $emailQuery->close();

        if ($emailData) {
            $to_email = $emailData['email'];
            $to_name = $emailData['full_name'];

            $subject = "【睿煬企業社】您的估價單已建立";
            $body = "
                <div style='font-family:Arial,sans-serif; color:#333; background:#f9f9f9; padding:20px; border-radius:8px; max-width:600px; margin:auto;'>
                    <h2 style='color:#2c3e50;'>親愛的 {$to_name}，您好：</h2>
                    <p>我們已經為您的愛車建立了一份新的估價單，以下是詳細內容：</p>
                    <ul>
                        <li>🚗 車牌號碼：{$license_plate}</li>
                        <li>📋 估價內容：{$items}</li>
                        <li>💰 總金額：NT$ {$total_price}</li>
                    </ul>
                    <p>如有任何疑問，歡迎與我們聯繫。</p>
                    <p style='margin-top:20px;'>睿煬企業社 敬上</p>
                </div>
            ";

            if (!sendEmail($to_email, $to_name, $subject, $body)) {
                $message .= "<br>⚡ 但寄送通知信件失敗，請檢查Email發送狀況！";
            }
        }

    } else {
        $message = "❌ 新增失敗：" . $insert->error;
    }

    $insert->close();
} else {
    $message = "❌ 查無對應車牌，無法新增估價單。";
}

// 抓取所有車牌及其對應使用者姓名
$query = "
    SELECT v.license_plate, u.full_name
    FROM vehicles v
    JOIN users u ON v.owner_id = u.user_id
    ORDER BY v.license_plate
";
$usersWithVehicles = [];
$sql = "
    SELECT u.user_id, u.full_name, v.license_plate
    FROM users u
    LEFT JOIN vehicles v ON u.user_id = v.owner_id
    WHERE u.role = 'customer'
    ORDER BY u.full_name, v.license_plate
";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $uid = $row['user_id'];
    $usersWithVehicles[$uid]['name'] = $row['full_name'];
    if (!empty($row['license_plate'])) {
        $usersWithVehicles[$uid]['plates'][] = $row['license_plate'];
    }
}
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>新增估價單</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f4f8;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 650px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
        }
        h2 {
            font-weight: bold;
            color: #2c3e50;
            border-left: 6px solid #3498db;
            padding-left: 15px;
            margin-bottom: 25px;
        }
        label.form-label {
            font-weight: bold;
            color: #34495e;
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .alert {
            font-weight: bold;
        }
        select.form-select,
        textarea.form-control,
        input.form-control {
            box-shadow: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>🧾 新增估價單</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form id="estimateForm" method="POST">
    <div class="mb-3">
    <label class="form-label">👤 選擇會員</label>
    <select id="memberSelect" class="form-select" onchange="updatePlates()" required>
        <option value="">請選擇會員</option>
        <?php foreach ($usersWithVehicles as $uid => $data): ?>
            <option value="<?= $uid ?>"><?= htmlspecialchars($data['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">🚗 選擇車牌</label>
    <select name="license_plate" id="plateSelect" class="form-select" required>
        <option value="">請先選擇會員</option>
    </select>
</div>


        <div class="mb-3">
            <label class="form-label">📋 估價內容</label>
            <textarea name="items" class="form-control" rows="4" placeholder="範例：更換輪胎、機油..." required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">💰 總金額 (NT$)</label>
            <input type="number" name="total_price" class="form-control" required min="0" step="1">
        </div>

        <div class="d-flex justify-content-between">
            <a href="<?= ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'staff_dashboard.php' ?>" class="btn btn-secondary">← 返回後台</a>
            <button type="button" class="btn btn-primary" onclick="previewEstimate()">新增估價單</button>
        </div>
    </form>
</div>
<script>
const userVehicles = <?= json_encode($usersWithVehicles) ?>;

function updatePlates() {
    const memberId = document.getElementById('memberSelect').value;
    const plateSelect = document.getElementById('plateSelect');
    plateSelect.innerHTML = '';

    if (!memberId || !userVehicles[memberId]) {
        plateSelect.innerHTML = '<option value=\"\">請先選擇會員</option>';
        return;
    }

    const plates = userVehicles[memberId]['plates'] || [];
    if (plates.length === 0) {
        plateSelect.innerHTML = '<option value=\"\">此會員尚無車輛</option>';
    } else {
        plateSelect.innerHTML = '<option value=\"\">請選擇車牌</option>';
        plates.forEach(plate => {
            const opt = document.createElement('option');
            opt.value = plate;
            opt.text = plate;
            plateSelect.appendChild(opt);
        });
    }
}
</script>
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h5 class="modal-title" id="previewModalLabel">📋 送出前確認</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>會員：</strong><span id="previewUser"></span></p>
        <p><strong>車牌號碼：</strong><span id="previewPlate"></span></p>
        <p><strong>估價內容：</strong><br><span id="previewItems"></span></p>
        <p><strong>總金額：</strong>NT$ <span id="previewTotal"></span></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
        <button class="btn btn-primary" onclick="document.getElementById('estimateForm').submit();">確認送出</button>
      </div>
    </div>
  </div>
</div>
<script>
function previewEstimate() {
    const memberSelect = document.getElementById('memberSelect');
    const plateSelect = document.getElementById('plateSelect');
    const items = document.querySelector('[name=\"items\"]').value;
    const total = document.querySelector('[name=\"total_price\"]').value;

    const memberName = memberSelect.options[memberSelect.selectedIndex].text;
    const plate = plateSelect.value;

    document.getElementById('previewUser').innerText = memberName;
    document.getElementById('previewPlate').innerText = plate;
    document.getElementById('previewItems').innerText = items;
    document.getElementById('previewTotal').innerText = total;

    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>