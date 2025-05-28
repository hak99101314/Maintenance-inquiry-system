<?php
// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制

// 檢查用戶是否已登入，未登入則重定向到登入頁面
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ========== 資料庫連線設定 ==========
$servername  = "localhost";
$dbUsername  = "root";
$dbPassword  = "";
$dbName      = "睿煬企業社";

// 建立資料庫連線
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 使用者資料查詢 ==========
$user_id = $_SESSION['user_id'];  // 取得目前登入的使用者 ID

// 查詢使用者基本資料
$sql = "SELECT full_name, contact_number, email FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// 查詢使用者的車輛資料
$sql = "SELECT license_plate, model FROM vehicles WHERE owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicles = [];
while ($row = $result->fetch_assoc()) {
    $vehicles[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>預約系統</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #2c3e50;
      font-size: 17px;
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

    .appointment-card {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      padding: 30px;
      margin: 60px auto;
      max-width: 650px;
    }

    .appointment-title {
      font-size: 26px;
      font-weight: bold;
      color: #2c3e50;
      text-align: center;
      border-bottom: 2px solid #f1c40f;
      padding-bottom: 10px;
      margin-bottom: 30px;
    }

    .form-label {
      font-weight: bold;
      color: #34495e;
    }

    .form-control, .form-select {
      border-radius: 6px;
      font-size: 16px;
    }

    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 0 0.25rem rgba(241, 196, 15, 0.3);
      border-color: #f1c40f;
    }

    .btn-primary {
      background-color: #2c3e50;
      border: none;
      font-size: 16px;
      padding: 12px;
      margin-top: 15px;
    }

    .btn-primary:hover {
      background-color: #f1c40f;
      color: #2c3e50;
      font-weight: bold;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">預約系統</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">首頁</a></li>
        <li class="nav-item"><a class="nav-link active" href="appointments.php">預約服務</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">用戶資料</a></li>
        <li class="nav-item"><a class="nav-link" href="maintenance-history.php">查詢維修紀錄</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <div class="appointment-card mx-auto">
    <h2 class="appointment-title">服務預約</h2>
    <form id="appointment-form">
      <div class="mb-3">
        <label for="name" class="form-label">姓名</label>
        <input type="text" class="form-control" id="name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">聯絡電話</label>
        <input type="tel" class="form-control" id="phone" pattern="09[0-9]{8}" value="<?= htmlspecialchars($user['contact_number']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="license_plate" class="form-label">選擇車輛</label>
        <select id="license_plate" class="form-select" required>
          <option value="">請選擇車牌</option>
          <?php foreach ($vehicles as $v): ?>
            <option value="<?= htmlspecialchars($v['license_plate']) ?>"><?= htmlspecialchars($v['license_plate']) ?>（<?= htmlspecialchars($v['model']) ?>）</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="service" class="form-label">選擇服務項目</label>
        <select class="form-select" id="service" required>
          <option value="">請選擇...</option>
          <option value="maintenance">一般維修</option>
          <option value="inspection">年度檢查</option>
          <option value="cleaning">車輛清潔</option>
        </select>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="date" class="form-label">選擇日期</label>
          <input type="date" class="form-control" id="date" min="" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="time" class="form-label">選擇時間</label>
          <input type="time" class="form-control" id="time" step="900" required>
          <small class="text-muted">每 15 分鐘為一時段。欲指定時間請電洽 0913-985808</small>
          <div id="slot-info" style="margin-top: 5px; font-size: 14px; color: #ff6b6b;"></div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">提交預約</button>
    </form>
  </div>
</div>

<!-- 引入 JS 套件 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const dateInput = document.getElementById('date');
  const timeInput = document.getElementById('time');
  const slotInfo = document.getElementById('slot-info');

  // ✅ 設定今日為最小可預約日期
  const today = new Date().toISOString().split("T")[0];
  dateInput.setAttribute("min", today);

  function checkAvailableSlots() {
    const date = dateInput.value;
    const time = timeInput.value;
    if (!date || !time) return slotInfo.textContent = '';
    fetch('api/checkAvailableSlots.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ date, time }),
      credentials: 'include'
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        if (data.available_slots > 0) {
          slotInfo.textContent = `此時段剩餘 ${data.available_slots} 個名額`;
          slotInfo.style.color = '#28a745';
        } else {
          slotInfo.textContent = '此時段已滿，請選擇其他時間';
          slotInfo.style.color = '#dc3545';
        }
      } else {
        slotInfo.textContent = '無法查詢名額';
        slotInfo.style.color = '#dc3545';
      }
    })
    .catch(() => {
      slotInfo.textContent = '系統錯誤，請稍後再試';
      slotInfo.style.color = '#dc3545';
    });
  }

  timeInput.addEventListener('change', function () {
    const [hh, mm] = this.value.split(":");
    let rounded = Math.round(parseInt(mm) / 15) * 15;
    let hour = parseInt(hh);
    if (rounded === 60) { rounded = 0; hour += 1; }
    this.value = `${String(hour).padStart(2, '0')}:${String(rounded).padStart(2, '0')}`;
    checkAvailableSlots();
  });

  dateInput.addEventListener('change', checkAvailableSlots);

  document.getElementById('appointment-form').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = {
      name: document.getElementById('name').value,
      phone: document.getElementById('phone').value,
      license_plate: document.getElementById('license_plate').value,
      service: document.getElementById('service').value,
      date: document.getElementById('date').value,
      time: document.getElementById('time').value
    };

    Swal.fire({
      title: '確定要送出預約嗎？',
      text: '請再次確認您的預約資訊',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: '確定預約',
      cancelButtonText: '取消'
    }).then((result) => {
      if (!result.isConfirmed) return;
      fetch('api/checkAvailableSlots.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ date: formData.date, time: formData.time }),
  credentials: 'include'
})
.then(res => res.json())
.then(data => {
  if (data.success && data.available_slots > 0) {
    return fetch('api/createAppointment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData),
      credentials: 'include'
    });
  } else {
    // ❌ 預約已滿，更新文字提示
    slotInfo.textContent = '此時段已滿，請選擇其他時間';
    slotInfo.style.color = '#dc3545';

    Swal.fire({
      icon: 'error',
      title: '預約失敗',
      text: `此時段已滿，剩餘名額：${data.available_slots || 0}`
    });
    throw new Error("Slot full");
  }
})
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire({ icon: 'success', title: '預約成功！', timer: 2000, showConfirmButton: false });
          document.getElementById('appointment-form').reset();
          slotInfo.textContent = '';
          setTimeout(() => window.location.href = 'dashboard.php', 2000);
        } else {
          Swal.fire({ icon: 'error', title: '預約失敗', text: data.message || '請稍後再試' });
        }
      })
      .catch(console.error);
    });
  });
});
</script>
</body>
</html>
