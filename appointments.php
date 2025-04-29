  <?php
  // ========== 會話管理與安全驗證 ==========
  session_start();  // 啟動會話管理機制

  // 檢查用戶是否已登入，未登入則重定向到登入頁面
  if (!isset($_SESSION['user_id'])) {
      header("Location: login.php");
      exit();
  }

  // ========== 資料庫連線設定 ==========
  // 設定資料庫連線參數
  $servername  = "localhost";
  $dbUsername  = "root";
  $dbPassword  = "karry,roy,jackson";
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

  // 查詢使用者的車輛資料（預設只取第一台）
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

  // 關閉資料庫連線
  $conn->close();
  ?>
  <!DOCTYPE html>
  <html lang="zh-Hant">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>預約系統</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
      /* 頁面基本樣式 */
      body {
        background: #f1f5f9;
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      }
      /* 預約卡片樣式 */
      .appointment-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 2rem;
        margin-top: 4rem;
        margin-bottom: 4rem;
      }
      /* 標題樣式 */
      .appointment-title {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 1.5rem;
        text-align: center;
      }
      /* 表單標籤樣式 */
      .form-label {
        font-weight: bold;
      }
      /* 表單輸入框聚焦效果 */
      .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25);
      }
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
    <!-- 導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
      <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">預約系統</a>
        <!-- 響應式選單按鈕 -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <!-- 導覽選單 -->
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

    <!-- 預約表單區塊 -->
    <div class="container">
      <div class="appointment-card mx-auto" style="max-width: 600px;">
        <h2 class="appointment-title">服務預約</h2>
        <!-- 預約表單 -->
        <form id="appointment-form">
          <!-- 姓名輸入欄位 -->
          <div class="mb-3">
            <label for="name" class="form-label">姓名</label>
            <input type="text" class="form-control" id="name" placeholder="請輸入您的姓名" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
          </div>
          <!-- 聯絡電話輸入欄位 -->
          <div class="mb-3">
            <label for="phone" class="form-label">聯絡電話</label>
            <input type="tel" class="form-control" id="phone" pattern="09[0-9]{8}" placeholder="09xxxxxxxx" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>" required>
          </div>
          <!-- 車牌號碼輸入欄位 -->
          <div class="mb-3">
  <label for="license_plate" class="form-label">選擇車輛</label>
  <select id="license_plate" class="form-select" required>
    <option value="">請選擇車牌</option>
    <?php foreach ($vehicles as $v): ?>
      <option value="<?= htmlspecialchars($v['license_plate']) ?>">
        <?= htmlspecialchars($v['license_plate']) ?>（<?= htmlspecialchars($v['model']) ?>）
      </option>
    <?php endforeach; ?>
  </select>
</div>

          <!-- 服務項目選擇 -->
          <div class="mb-3">
            <label for="service" class="form-label">選擇服務項目</label>
            <select class="form-select" id="service" required>
              <option value="">請選擇...</option>
              <option value="maintenance">一般維修</option>
              <option value="inspection">年度檢查</option>
              <option value="cleaning">車輛清潔</option>
            </select>
          </div>
          <!-- 日期和時間選擇 -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="date" class="form-label">選擇日期</label>
              <input type="date" class="form-control" id="date" required>
            </div>
            <div class="col-md-6 mb-3">
  <label for="time" class="form-label">選擇時間</label>
  <input type="time" class="form-control" id="time" required>
  <div id="slot-info" style="margin-top: 5px; font-size: 14px; color: #ff6b6b;"></div>
</div>

          </div>
          <!-- 提交按鈕 -->
          <button type="submit" class="btn btn-primary w-100">提交預約</button>
        </form>
      </div>
    </div>

    <!-- 前端 JavaScript -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
  const dateInput = document.getElementById('date');
  const timeInput = document.getElementById('time');
  const slotInfo = document.getElementById('slot-info');

  function checkAvailableSlots() {
    const date = dateInput.value;
    const time = timeInput.value;

    if (!date || !time) {
      slotInfo.textContent = '';
      return;
    }

    fetch('api/checkAvailableSlots.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ date, time }),
      credentials: 'include'
    })
    .then(response => response.json())
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
      }
    })
    .catch(error => {
      console.error('錯誤:', error);
      slotInfo.textContent = '系統錯誤，請稍後再試';
    });
  }

  dateInput.addEventListener('change', checkAvailableSlots);
  timeInput.addEventListener('change', checkAvailableSlots);

  document.getElementById('appointment-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = {
      name: document.getElementById('name').value,
      phone: document.getElementById('phone').value,
      license_plate: document.getElementById('license_plate').value,
      service: document.getElementById('service').value,
      date: document.getElementById('date').value,
      time: document.getElementById('time').value
    };

    const submitBtn = document.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    // 🔥 在真正送出之前，先跳一個 SweetAlert2 確認視窗
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
      if (result.isConfirmed) {
        // 使用者按了「確定預約」
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 提交中...';

        // 先查詢是否還有名額
        fetch('api/checkAvailableSlots.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ date: formData.date, time: formData.time }),
          credentials: 'include'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            if (data.available_slots > 0) {
              // 還有名額，送出正式預約
              return fetch('api/createAppointment.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData),
                credentials: 'include'
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: '人數已滿',
                text: '此3小時時段預約人數已滿，請選擇其他時間',
                confirmButtonColor: '#d33'
              });
              throw new Error('名額已滿');
            }
          } else {
            Swal.fire({
              icon: 'error',
              title: '錯誤',
              text: '無法查詢名額，請稍後再試',
              confirmButtonColor: '#d33'
            });
            throw new Error('查詢名額失敗');
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: '預約成功！',
              text: '2秒後自動返回首頁...',
              timer: 2000,
              timerProgressBar: true,
              showConfirmButton: false
            }).then(() => {
              window.location.href = 'dashboard.php'; // 預約成功自動跳回首頁
            });
            document.getElementById('appointment-form').reset();
            slotInfo.textContent = '';
          } else {
            Swal.fire({
              icon: 'error',
              title: '預約失敗',
              text: data.message,
              confirmButtonColor: '#d33'
            });
          }
        })
        .catch(error => {
          console.error('錯誤:', error);
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        });
      }
      // 如果使用者點取消，就什麼都不做
    });
  });
});


    </script>
    
    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  </body>
  </html>
