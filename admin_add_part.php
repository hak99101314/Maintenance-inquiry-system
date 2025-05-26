<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$dbname = "睿煬企業社";
$username = "root";
$password = "karry,roy,jackson";  // ← 改為 "karry,roy,jackson" 若需連接正式資料庫
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連線失敗：" . $conn->connect_error);
}

$message = "";
$usersWithVehicles = [];

$appointment_user_id = null;
$appointment_plate = null;

// 如果有帶入 appointment_id，就查出對應的會員與車牌
if (isset($_GET['appointment_id'])) {
    $aid = intval($_GET['appointment_id']);

    $stmt = $conn->prepare("
        SELECT u.user_id, u.full_name, v.license_plate
        FROM appointments a
        JOIN vehicles v ON a.vehicle_id = v.vehicle_id
        JOIN users u ON v.owner_id = u.user_id
        WHERE a.appointment_id = ?
    ");
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $stmt->bind_result($appointment_user_id, $appointment_user_name, $appointment_plate);
    $stmt->fetch();
    $stmt->close();
}

// 查詢會員與車輛清單
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

// 📥 表單處理
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $license_plate = $_POST['license_plate'] ?? $_POST['license_plate_hidden'] ?? '';
    $items = $_POST['items'];  // 字串
    $total_price = floatval($_POST['total_price']);

    $stmt = $conn->prepare("SELECT owner_id FROM vehicles WHERE license_plate = ?");
    $stmt->bind_param("s", $license_plate);
    $stmt->execute();
    $stmt->bind_result($member_id);
    $stmt->fetch();
    $stmt->close();

    if ($member_id) {
        $insert = $conn->prepare("INSERT INTO estimates (member_id, license_plate, items, total_price, created_by) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("issdi", $member_id, $license_plate, $items, $total_price, $_SESSION['user_id']);
        if ($insert->execute()) {
            $message = "✅ 估價單新增成功！";
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

                // Email HTML 格式
                $itemLines = explode("\n", $items);
                $itemListHtml = "<ul style='line-height:1.8;'>";
                foreach ($itemLines as $line) {
                    $itemListHtml .= "<li>" . htmlspecialchars($line) . "</li>";
                }
                $itemListHtml .= "</ul>";

                $body = "
                    <div style='font-family:Arial,sans-serif; padding:20px;'>
                        <h2>親愛的 {$to_name}，您好：</h2>
                        <p>我們已為您的愛車建立新的估價單：</p>
                        <p>🚗 車牌號碼：{$license_plate}</p>
                        <p>📋 項目明細如下：</p>
                        {$itemListHtml}
                        <p>💰 總金額：<strong>NT$ {$total_price}</strong></p>
                        <p>如有疑問，歡迎與我們聯繫。</p>
                        <p>連絡電話：0913-985808</p>
                    </div>
                ";
                if (!sendEmail($to_email, $to_name, $subject, $body)) {
                    $message .= "<br>⚡ 但通知信寄出失敗！";
                }
            }
        } else {
            $message = "❌ 新增失敗：" . $insert->error;
        }
        $insert->close();
    } else {
        $message = "❌ 查無對應車牌，無法新增估價單。";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>新增估價單 - 睿煬企業社</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background-color: #f5f6fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .form-container {
      max-width: 800px;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      margin: 40px auto;
    }
    h2 {
      font-weight: bold;
      text-align: center;
      margin-bottom: 30px;
      color: #2c3e50;
      border-bottom: 2px solid #f1c40f;
      padding-bottom: 10px;
    }
    label {
      font-weight: bold;
    }
    .btn-primary {
      background-color: #2c3e50;
      border: none;
    }
    .btn-primary:hover {
      background-color: #f1c40f;
      color: #2c3e50;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="form-container">
  <h2>🧾 新增估價單</h2>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>



  <div class="mb-3">
    <label>🚗 選擇車牌</label>
    <select name="license_plate" id="plateSelect" class="form-select" required <?= isset($appointment_plate) ? 'disabled' : '' ?>>
    <?php if (isset($appointment_plate)): ?>
  <input type="hidden" name="license_plate" value="<?= $appointment_plate ?>">
<?php endif; ?>

      <option value="">請先選擇會員</option>
    </select>
  </div>

  <div class="mb-3">
    <label>📋 維修項目明細</label>
    <div class="table-responsive">
      <table class="table table-bordered text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>維修項目</th>
            <th>規格</th>
            <th>數量</th>
            <th>單價</th>
            <th>小計</th>
            <th>備註</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody id="itemsBody">
          <tr>
            <td><input type="text" class="form-control" name="item_name[]"></td>
            <td><input type="text" class="form-control" name="item_spec[]"></td>
            <td><input type="number" class="form-control" name="item_qty[]" oninput="calculateSubtotal(this)"></td>
            <td><input type="number" class="form-control" name="item_price[]" oninput="calculateSubtotal(this)"></td>
            <td><input type="number" class="form-control" name="item_total[]" readonly></td>
            <td><input type="text" class="form-control" name="item_note[]"></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">刪除</button></td>
          </tr>
        </tbody>
      </table>
    </div>
    <button type="button" class="btn btn-secondary btn-sm" onclick="addRow()">➕ 新增項目</button>
  </div>

  <input type="hidden" name="items" id="items">

  <div class="mb-3">
    <label>💰 總金額 (NT$)</label>
    <input type="number" name="total_price" id="total_price" class="form-control" required min="0" step="1" readonly>
  </div>

  <div class="d-flex justify-content-between mt-4">
    <a href="<?= ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'staff_dashboard.php' ?>" class="btn btn-secondary">
      ← 返回後台
    </a>
    <button type="button" class="btn btn-primary" onclick="confirmSubmit()">送出估價單</button>
  </div>
</form>
<script>
const userVehicles = <?= json_encode($usersWithVehicles) ?>;

function updatePlates() {
  const memberId = document.getElementById('memberSelect').value;
  const plateSelect = document.getElementById('plateSelect');
  plateSelect.innerHTML = '';

  if (!memberId || !userVehicles[memberId]) {
    plateSelect.innerHTML = '<option value="">請先選擇會員</option>';
    return;
  }

  const plates = userVehicles[memberId]['plates'] || [];
  if (plates.length === 0) {
    plateSelect.innerHTML = '<option value="">此會員尚無車輛</option>';
  } else {
    plateSelect.innerHTML = '<option value="">請選擇車牌</option>';
    plates.forEach(plate => {
      const opt = document.createElement('option');
      opt.value = plate;
      opt.text = plate;
      plateSelect.appendChild(opt);
    });
  }
}

function addRow() {
  const tbody = document.getElementById("itemsBody");
  const row = document.createElement("tr");
  row.innerHTML = `
    <td><input type="text" class="form-control" name="item_name[]"></td>
    <td><input type="text" class="form-control" name="item_spec[]"></td>
    <td><input type="number" class="form-control" name="item_qty[]" oninput="calculateSubtotal(this)"></td>
    <td><input type="number" class="form-control" name="item_price[]" oninput="calculateSubtotal(this)"></td>
    <td><input type="number" class="form-control" name="item_total[]" readonly></td>
    <td><input type="text" class="form-control" name="item_note[]"></td>
    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">刪除</button></td>
  `;
  tbody.appendChild(row);
}

function removeRow(button) {
  const row = button.closest("tr");
  row.remove();
  updateTotal();
}

function calculateSubtotal(input) {
  const row = input.closest("tr");
  const qty = parseFloat(row.querySelector("input[name='item_qty[]']").value) || 0;
  const price = parseFloat(row.querySelector("input[name='item_price[]']").value) || 0;
  const subtotal = qty * price;
  row.querySelector("input[name='item_total[]']").value = subtotal.toFixed(0);
  updateTotal();
}

function updateTotal() {
  let total = 0;
  document.querySelectorAll("input[name='item_total[]']").forEach(input => {
    total += parseFloat(input.value) || 0;
  });
  document.getElementById("total_price").value = total.toFixed(0);
}

function confirmSubmit() {
  const rows = document.querySelectorAll("#itemsBody tr");
  let itemDetails = [];

  rows.forEach(row => {
    const name = row.querySelector("input[name='item_name[]']").value.trim();
    const spec = row.querySelector("input[name='item_spec[]']").value.trim();
    const qty = row.querySelector("input[name='item_qty[]']").value || 0;
    const price = row.querySelector("input[name='item_price[]']").value || 0;
    const total = row.querySelector("input[name='item_total[]']").value || 0;
    const note = row.querySelector("input[name='item_note[]']").value.trim();
    const text = `${name}（${spec}）×${qty} @${price}元 = ${total}元${note ? '，備註：' + note : ''}`;
    itemDetails.push(text);
  });

  document.getElementById('items').value = itemDetails.join("\n");

  Swal.fire({
    title: '確認送出',
    text: "請再次確認估價單資料是否正確",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#2c3e50',
    cancelButtonColor: '#888',
    confirmButtonText: '送出',
    cancelButtonText: '取消'
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('estimateForm').submit();
    }
  });
}
</script>



</body>
</html>
