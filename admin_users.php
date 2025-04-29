<?php
// ========== 會話管理與安全驗證 ==========
// 檢查會話是否已啟動，如果沒有則啟動會話
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// 檢查用戶是否已登入，未登入則重新導向到登入頁面
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ========== 資料庫連線設定 ==========
// 設定資料庫連接參數
$servername = "localhost";    // 資料庫伺服器位置
$dbUsername = "root";        // 資料庫使用者名稱
$dbPassword = "karry,roy,jackson";            // 資料庫密碼
$dbName = "睿煬企業社";      // 資料庫名稱

// 建立資料庫連線並檢查是否成功
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}

// ========== 資料查詢 ==========
// 查詢所有用戶資料
$sql = "SELECT user_id, username, full_name, email, role FROM users";
$result = $conn->query($sql);
$users = [];

// 將查詢結果存入陣列
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
        $users[] = $row;
    }
}

// 關閉資料庫連線
$conn->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <!-- 確保網頁在各種裝置上都能正確顯示 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用戶管理 - 管理員系統</title>
    <!-- 引入 Bootstrap 5 的 CSS 框架 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入後台專用的自定義樣式 -->
    <link rel="stylesheet" href="assets/css/admin_styles.css">
</head>
<style>
/* UPDATED - 模態框樣式 */
.modal-content {
    background-color: #f7f9fc;
    border-radius: 10px;
    border: none;
}
.modal-header {
    background-color: #eef2f7;
    border-bottom: 1px solid #ced4da;
}
.modal-title {
    font-weight: 600;
}
.modal-body {
    padding: 1.5rem;
}
.modal-body .form-label {
    font-weight: 500;
}
.modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 0.75rem 1.5rem;
}
</style>

<body>
    <!-- ========== 導覽列區域 ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <!-- 網站標題 -->
            <a class="navbar-brand" href="#">管理員系統</a>
            <!-- 響應式選單按鈕 -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- 導覽選單 -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">首頁</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_maintenance.php">維修管理</a></li>
                    <li class="nav-item"><a class="nav-link active" href="admin_users.php">使用者管理</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_settings.php">系統設定</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== 主要內容區域 ========== -->
    <div class="container mt-4">
        <h2 class="text-center mb-4">使用者管理</h2>
        <!-- 用戶資料表格 -->
        <!-- 用戶資料表格 -->
<table class="table table-hover table-light"> <!-- UPDATED -->
    <thead class="table-secondary"> <!-- 淺灰色表頭 -->
        <tr>
            <th>用戶ID</th>
            <th>帳號</th>
            <th>姓名</th>
            <th>電子郵件</th>
            <th>角色</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody id="user-table-body">
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= htmlspecialchars($user['user_id']) ?>)">編輯</button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?= htmlspecialchars($user['user_id']) ?>)">刪除</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center">目前無用戶資料</td></tr>
        <?php endif; ?>
    </tbody>
</table>

        <!-- 新增用戶按鈕 -->
        <button class="btn btn-primary w-100" id="add-user-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">新增用戶</button>
    </div>

    <!-- ========== 新增用戶模態框 ========== -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content"> <!-- UPDATED -->
      <div class="modal-header">
        <h5 class="modal-title" id="addUserModalLabel">新增用戶</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body"> <!-- UPDATED -->
        <form id="add-user-form" action="add_user.php" method="POST">
          <!-- 輸入欄位 -->
          <div class="mb-3">
            <label class="form-label">帳號</label>
            <input type="text" class="form-control" name="username" required>
          </div>
          <div class="mb-3">
            <label class="form-label">姓名</label>
            <input type="text" class="form-control" name="full_name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">電子郵件</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">角色</label>
            <select class="form-control" name="role" required>
              <option value="customer">會員</option>
              <option value="staff">員工</option>
              <option value="admin">管理員</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">密碼</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">保存</button>
        </form>
      </div>
    </div>
  </div>
</div>

                        <!-- 提交按鈕 -->
                        <button type="submit" class="btn btn-primary w-100">保存</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== 編輯用戶模態框 ========== -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">編輯用戶</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- 編輯用戶表單 -->
                    <form id="edit-user-form" action="update_user.php" method="POST">
                        <!-- 隱藏欄位存放用戶 ID -->
                        <input type="hidden" id="edit-user-id" name="user_id">
                        <!-- 帳號輸入欄位（唯讀） -->
                        <div class="mb-3">
                            <label for="edit-username" class="form-label">帳號</label>
                            <input type="text" class="form-control" id="edit-username" name="username" readonly>
                        </div>
                        <!-- 姓名輸入欄位 -->
                        <div class="mb-3">
                            <label for="edit-full-name" class="form-label">姓名</label>
                            <input type="text" class="form-control" id="edit-full-name" name="full_name" required>
                        </div>
                        <!-- 電子郵件輸入欄位 -->
                        <div class="mb-3">
                            <label for="edit-email" class="form-label">電子郵件</label>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                        </div>
                        <!-- 角色選擇下拉選單 -->
                        <div class="mb-3">
                            <label for="edit-role" class="form-label">角色</label>
                            <select class="form-control" id="edit-role" name="role" required>
                                <option value="customer">會員</option>
                                <option value="staff">員工</option>
                                <option value="admin">管理員</option>
                            </select>
                        </div>
                        <!-- 新密碼輸入欄位（選填） -->
                        <div class="mb-3">
                            <label for="edit-password" class="form-label">新密碼</label>
                            <input type="password" class="form-control" id="edit-password" name="password" placeholder="如果不需要更改密碼，請留空">
                        </div>
                        <!-- 提交按鈕 -->
                        <button type="submit" class="btn btn-primary w-100">保存更改</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== JavaScript 功能 ========== -->
    <script>
    // 編輯用戶功能
    function editUser(userId) {
        // 使用 AJAX 呼叫 get_user.php 取得用戶資料
        fetch('get_user.php?id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 將用戶資料填入表單
                    document.getElementById('edit-user-id').value = data.user.user_id;
                    document.getElementById('edit-username').value = data.user.username;
                    document.getElementById('edit-full-name').value = data.user.full_name;
                    document.getElementById('edit-email').value = data.user.email;
                    document.getElementById('edit-role').value = data.user.role;
                    // 顯示編輯模態框
                    new bootstrap.Modal(document.getElementById('editUserModal')).show();
                } else {
                    alert('無法讀取用戶資料：' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('系統錯誤，請稍後再試');
            });
    }

    // 刪除用戶功能
    function deleteUser(userId) {
        // 顯示確認對話框
        if (confirm('確定要刪除此用戶嗎？')) {
            // 使用 AJAX 呼叫 delete_user.php 刪除用戶
            fetch('delete_user.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('用戶已刪除');
                        window.location.reload();  // 重新載入頁面
                    } else {
                        alert(data.message || '刪除失敗');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('系統錯誤，請稍後再試');
                });
        }
    }
    </script>
    <!-- 引入 Bootstrap 5 的 JavaScript 框架 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
