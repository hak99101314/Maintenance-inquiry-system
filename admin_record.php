<?php
// ========== 初始化設定 ==========
// admin_record.php
session_start();  // 啟動會話機制，用於管理使用者登入狀態

// 檢查使用者是否已登入
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // 若未登入則重新導向到登入頁面
    exit();
}

// 權限檢查：只允許管理員和維修人員存取
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff') {
    echo "<script>alert('您沒有權限存取此頁面'); window.location.href = 'dashboard.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>新增維修紀錄 - 管理員系統</title>
  <!-- 引入外部資源 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/admin_styles.css">
  
  <!-- ========== 頁面樣式設定 ========== -->
  <style>
    /* 維修表單容器樣式 */
    .repair-form {
      max-width: 800px;
      margin: 20px auto;
      padding: 20px;
      border: 1px solid #ddd;
    }
    
    /* 表單標題區域樣式 */
    .form-header {
      text-align: center;
      margin-bottom: 20px;
      border-bottom: 2px solid #000;
      padding-bottom: 10px;
    }
    
    /* 客戶資訊區域網格布局 */
    .customer-info {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
      margin-bottom: 20px;
    }
    
    /* 維修項目表格樣式 */
    .repair-table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }
    
    /* 表格邊框和內距設定 */
    .repair-table th, .repair-table td {
      border: 1px solid #000;
      padding: 8px;
      text-align: center;
    }
    
    /* 表格標題列背景色 */
    .repair-table th {
      background-color: #f8f9fa;
    }
    
    /* 注意事項文字樣式 */
    .notes {
      font-size: 0.9em;
      margin-top: 20px;
    }
    
    /* 資訊列彈性布局 */
    .info-row {
      display: flex;
      margin-bottom: 10px;
    }
    
    /* 資訊項目樣式 */
    .info-item {
      flex: 1;
      display: flex;
      align-items: center;
    }
    
    /* 標籤寬度設定 */
    .info-item label {
      min-width: 70px;
      margin-right: 10px;
    }
    
    /* 輸入欄位彈性設定 */
    .info-item input {
      flex: 1;
    }
    
    /* 簽名和金額區域樣式 */
    .signature-amount {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 20px 0;
    }
    
    /* 簽名和金額群組樣式 */
    .signature-amount .form-group {
      display: flex;
      align-items: center;
    }
    
    /* 標籤樣式調整 */
    .signature-amount label {
      margin-right: 10px;
      white-space: nowrap;
    }
    body {
  background-color: #f5f6fa;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #2c3e50;
  font-size: 17px;
}

h2 {
  font-weight: bold;
  color: #2c3e50;
  border-bottom: 2px solid #f1c40f;
  padding-bottom: 10px;
  margin-bottom: 20px;
  text-align: center;
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

.repair-form {
  background-color: #ffffff;
  border-radius: 12px;
  box-shadow: 0 0 20px rgba(0,0,0,0.05);
  padding: 30px;
  margin-top: 40px;
}

.info-item label {
  font-weight: bold;
  color: #2c3e50;
}
.info-item input, .info-item select, textarea {
  border-radius: 6px;
  font-size: 16px;
}

.repair-table th {
  background-color: #2c3e50;
  color: white;
  font-weight: bold;
}
.repair-table td, .repair-table th {
  padding: 12px;
  font-size: 16px;
}
.repair-table tr:hover {
  background-color: #f8f8f8;
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

.btn-secondary {
  background-color: #6c757d;
}
.btn-secondary:hover {
  background-color: #999;
}

.notes p {
  margin-bottom: 4px;
  font-size: 14px;
  color: #555;
}

@media print {
  .navbar,
  .btn,
  input[type="button"] {
    display: none !important;
  }

  .repair-form {
    border: none;
    margin: 0;
    padding: 0;
    box-shadow: none;
  }

  .repair-table, .repair-table th, .repair-table td {
    border: 1px solid black !important;
  }

  body {
    font-size: 13pt;
    background-color: white;
    color: black;
  }
}

    /* ========== 列印樣式設定 ========== */
    @media print {
      /* 隱藏不需要列印的元素 */
      .navbar,
      .btn,
      input[type="button"] {
          display: none !important;
      }
      
      /* 調整列印時的表單樣式 */
      .repair-form {
          border: none;
          margin: 0;
          padding: 0;
          box-shadow: none;
      }
      
      /* 確保表格邊框在列印時可見 */
      .repair-table,
      .repair-table th,
      .repair-table td {
          border: 1px solid black !important;
      }
      
      /* 調整列印時的字體大小 */
      body {
          font-size: 12pt;
      }
      
      /* 設定分頁符 */
      .repair-form {
          page-break-after: always;
      }
    }
  </style>
</head>
<body>
  <!-- ========== 導覽列 ========== -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <!-- 網站標題 -->
      <a class="navbar-brand" href="staff_dashboard.php">
        <i class="fas fa-tools me-2"></i>員工&管理員系統
      </a>
      <!-- 手機版選單按鈕 -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <!-- 導覽列選單 -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
        <!-- 導覽列選單 -->
<div class="collapse navbar-collapse" id="navbarNav">
  <ul class="navbar-nav ms-auto">
    <li class="nav-item">
      <a class="nav-link" href="<?php echo ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'staff_dashboard.php'; ?>">首頁</a>
    </li>
    <li class="nav-item">
      <a class="nav-link active" href="admin_record.php">新增維修紀錄</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="logout.php" id="logoutBtn">登出</a>
    </li>
  </ul>
</div>

    </div>
  </nav>
  
  <!-- ========== 主要內容區域 ========== -->
  <div class="container">
    <!-- 維修表單 -->
    <div class="repair-form">
      <!-- 表單標題 -->
      <div class="form-header">
        <h2>睿煬 汽車修護廠</h2>
        <div>服務專線：0913-985808</div>
      </div>
      
      <!-- 維修紀錄表單 -->
<form id="repair-form" method="POST" action="api/maintenance/create.php">
  <input type="hidden" id="userRole" value="<?= $_SESSION['role'] ?>">

        <!-- 客戶基本資料區域 -->
        <div class="info-row">
          <div class="info-item">
            <label>車主：</label>
            <input type="text" class="form-control" name="owner" required>
          </div>
          <div class="info-item">
            <label>電話：</label>
            <input type="text" class="form-control" name="phone">
          </div>
        </div>
        
        <!-- 車輛資訊區域 -->
        <div class="info-row">
          <div class="info-item">
            <label>車號：</label>
            <input type="text" class="form-control" name="plate_number" required>
          </div>
          <div class="info-item">
            <label>行動：</label>
            <input type="text" class="form-control" name="mobile">
          </div>
        </div>
        
        <!-- 車輛詳細資訊 -->
        <div class="info-row">
          <div class="info-item">
            <label>車型：</label>
            <input type="text" class="form-control" name="car_model">
          </div>
          <div class="info-item">
            <label>日期：</label>
            <input type="date" class="form-control" name="date" required>
          </div>
        </div>
        
        <!-- 車輛狀態資訊 -->
        <div class="info-row">
          <div class="info-item">
            <label>年份：</label>
            <input type="text" class="form-control" name="year">
          </div>
          <div class="info-item">
            <label>公里數：</label>
            <input type="text" class="form-control" name="mileage">
          </div>
        </div>
        
        <!-- 維修項目表格 -->
        <table class="repair-table">
          <thead>
            <tr>
              <th>維修項目</th>
              <th>規格</th>
              <th>數量</th>
              <th>單價</th>
              <th>小計</th>
              <th>備註</th>
            </tr>
          </thead>
          <tbody id="repair-items">
            <!-- 動態新增的維修項目將在此顯示 -->
          </tbody>
        </table>
        
        <!-- 新增維修項目按鈕 -->
        <button type="button" class="btn btn-secondary mb-3" onclick="addRepairItem()">新增維修項目</button>
        
        <!-- 建議事項輸入區 -->
        <div class="mb-3">
          <label>建議事項：</label>
          <textarea class="form-control" name="suggestions" rows="2"></textarea>
        </div>
        
        <!-- 簽名和金額區域 -->
        <div class="signature-amount">
          <div class="form-group">
            <label>客戶簽名：</label>
            <input type="text" class="form-control" name="customer_signature" style="width: 150px;">
          </div>
          <div class="form-group">
            <label>總計金額：</label>
            <input type="text" class="form-control" name="total_amount" style="width: 150px;" readonly>
          </div>
        </div>
        
        <!-- 注意事項說明 -->
        <div class="notes">
          <p>※1. 本維修單不含營業加值稅，如需開立發票，請自行填寫5%營業稅。</p>
          <p>※2. 委修期間車輛如遇天災、人禍及其他不可抗拒之事而發生損壞，本公司恕不負責。</p>
          <p>※3. 委修期間，維修車輛內之重要物品請自行保管、擔保，本公司恕不負保管之責任。</p>
          <p>※4. 委修車輛如有維修之問題，請於交車後三日內回廠檢修，逾期視同維修無誤。</p>
        </div>
        
        <!-- 表單操作按鈕 -->
        <div class="text-center mt-4">
          <button type="submit" class="btn btn-primary btn-lg me-2">儲存維修紀錄</button>
          <button type="button" class="btn btn-secondary btn-lg" onclick="printRecord()">
            <i class="fas fa-print me-1"></i>列印維修單
          </button>
        </div>
      </form>
    </div>
  </div>
  
  <!-- ========== JavaScript 程式碼 ========== -->
  <!-- 引入外部函式庫 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="assets/js/get_vehicle_info.js"></script>
  
  <script>
    // 新增維修項目到表格
    function addRepairItem() {
      const tbody = document.getElementById('repair-items');
      const newRow = document.createElement('tr');
      // 設定新行的 HTML 內容
      newRow.innerHTML = `
        <td><input type="text" class="form-control" name="item[]"></td>
        <td><input type="text" class="form-control" name="spec[]"></td>
        <td><input type="number" class="form-control" name="quantity[]" onchange="calculateSubtotal(this)"></td>
        <td><input type="number" class="form-control" name="price[]" onchange="calculateSubtotal(this)"></td>
        <td><input type="number" class="form-control" name="subtotal[]" readonly></td>
        <td><input type="text" class="form-control" name="note[]"></td>
      `;
      tbody.appendChild(newRow);
    }

    // 頁面載入時自動新增一個維修項目
    document.addEventListener('DOMContentLoaded', function() {
      addRepairItem();
    });

    // 計算單項維修的小計金額
    function calculateSubtotal(input) {
      const row = input.closest('tr');
      const quantity = parseFloat(row.querySelector('[name="quantity[]"]').value) || 0;
      const price = parseFloat(row.querySelector('[name="price[]"]').value) || 0;
      const subtotal = quantity * price;
      row.querySelector('[name="subtotal[]"]').value = subtotal;
      calculateTotal();  // 重新計算總金額
    }

    // 計算維修總金額
    function calculateTotal() {
      const subtotals = [...document.getElementsByName('subtotal[]')];
      const total = subtotals.reduce((sum, input) => sum + (parseFloat(input.value) || 0), 0);
      document.getElementsByName('total_amount')[0].value = total;
    }

    // 列印維修單功能
    function printRecord() {
      // 自動填入當前日期（如果未填寫）
      if (!document.getElementsByName('date')[0].value) {
        const today = new Date().toISOString().split('T')[0];
        document.getElementsByName('date')[0].value = today;
      }
      
      // 執行列印
      window.print();
    }

    // 表單提交處理
    document.getElementById('repair-form').addEventListener('submit', function(e) {
      e.preventDefault();  // 防止表單直接提交
      
      // 顯示確認對話框
      Swal.fire({
        title: '確認儲存',
        text: "是否要儲存此維修紀錄？",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1a4f95',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '確定儲存',
        cancelButtonText: '取消'
      }).then((result) => {
        if (result.isConfirmed) {
          saveRecord();  // 執行儲存操作
        }
      });
    });
    
    // 儲存維修紀錄
    async function saveRecord() {
      try {
        // 收集表單資料
        const formData = new FormData(document.getElementById('repair-form'));
        
        // 發送 API 請求
        const response = await fetch('api/maintenance/create.php', {
          method: 'POST',
          body: formData
        });
  
        if (!response.ok) {
          throw new Error('伺服器錯誤，無法儲存資料');
        }
  
        const result = await response.json();
  
        if (result.status === 'success') {
          // 儲存成功後詢問是否要列印
          Swal.fire({
  title: '儲存成功',
  text: '是否要列印維修單？',
  icon: 'success',
  showCancelButton: true,
  confirmButtonColor: '#1a4f95',
  cancelButtonColor: '#6c757d',
  confirmButtonText: '立即列印',
  cancelButtonText: '稍後列印'
}).then((action) => {
  if (action.isConfirmed) {
    printRecord();  // 執行列印
  } else {
    // 根據角色導向
    const role = document.getElementById('userRole').value;
    if (role === 'admin') {
      window.location.href = 'admin_dashboard.php';
    } else {
      window.location.href = 'staff_dashboard.php';
    }
  }
});

        } else {
          throw new Error(result.message || '儲存失敗，請稍後再試');
        }
      } catch (error) {
        // 錯誤處理
        Swal.fire({
          title: '錯誤',
          text: error.message,
          icon: 'error',
          confirmButtonColor: '#1a4f95'
        });
      }
    }
  </script>
</body>
</html>
