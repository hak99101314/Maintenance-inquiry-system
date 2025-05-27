<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "睿煬企業社");
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// ✅ 處理標記或取消已處理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_processed'], $_POST['estimate_id'])) {
    $eid = intval($_POST['estimate_id']);
    $current = intval($_POST['current']);
    $newStatus = $current === 1 ? 0 : 1;
    $conn->query("UPDATE estimates SET processed = $newStatus WHERE estimate_id = $eid");
}

$keyword = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$processed_filter = $_GET['processed'] ?? '';

$sql = "
    SELECT e.*, u.full_name
    FROM estimates e
    JOIN users u ON e.member_id = u.user_id
    WHERE 1 ";

if (!empty($keyword)) {
    $sql .= " AND u.full_name LIKE '%" . $conn->real_escape_string($keyword) . "%' ";
}
if (!empty($status_filter)) {
    $sql .= " AND e.status = '" . $conn->real_escape_string($status_filter) . "' ";
}
if ($processed_filter === '1') {
    $sql .= " AND e.processed = 1 ";
} elseif ($processed_filter === '0') {
    $sql .= " AND (e.processed IS NULL OR e.processed = 0) ";
}

$sql .= " ORDER BY e.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>會員估價單管理</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .star-button {
      background: none;
      border: none;
      font-size: 20px;
      color: gold;
      cursor: pointer;
    }
  </style>
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2 class="mb-4">📋 會員估價確認狀況</h2>

  <!-- 搜尋區塊 -->
  <form class="row g-3 mb-4" method="GET">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="搜尋會員姓名" value="<?= htmlspecialchars($keyword) ?>">
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select">
        <option value="">全部狀態</option>
        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>待確認</option>
        <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>已確認</option>
        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>已取消</option>
      </select>
    </div>
    <div class="col-md-3">
      <select name="processed" class="form-select">
        <option value="">全部狀態</option>
        <option value="1" <?= $processed_filter === '1' ? 'selected' : '' ?>>僅顯示已標記</option>
        <option value="0" <?= $processed_filter === '0' ? 'selected' : '' ?>>僅顯示未標記</option>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">搜尋</button>
    </div>
  </form>

  <table class="table table-bordered bg-white shadow-sm">
    <thead class="table-dark">
      <tr>
        <th>會員姓名</th>
        <th>車牌號碼</th>
        <th>總金額</th>
        <th>狀態</th>
        <th>標記</th>
        <th>建立時間</th>
        <th>操作</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['full_name']) ?></td>
        <td><?= htmlspecialchars($row['license_plate'] ?? '無資料') ?></td>
        <td>NT$ <?= number_format($row['total_price']) ?></td>
        <td>
          <?php
          $status = $row['status'] ?? 'pending';
          echo match($status) {
            'confirmed' => '<span class="badge bg-success">已確認</span>',
            'cancelled' => '<span class="badge bg-danger">已取消</span>',
            default => '<span class="badge bg-warning text-dark">待確認</span>'
          };
          ?>
        </td>
        <td class="text-center">
          <form method="POST">
            <input type="hidden" name="estimate_id" value="<?= $row['estimate_id'] ?>">
            <input type="hidden" name="current" value="<?= $row['processed'] ?? 0 ?>">
            <button name="toggle_processed" class="star-button">
              <?= ($row['processed'] ?? 0) ? '⭐' : '☆' ?>
            </button>
          </form>
        </td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
          <button 
            class="btn btn-sm btn-info text-white"
            onclick="showDetailModal('<?= htmlspecialchars($row['full_name']) ?>', '<?= htmlspecialchars($row['license_plate'] ?? '') ?>', '<?= nl2br(htmlspecialchars($row['items'])) ?>', '<?= number_format($row['total_price']) ?>', '<?= $status ?>', '<?= $row['created_at'] ?>')">
            🔍 查看內容
          </button>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
  <a href="<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'staff_dashboard.php' ?>" class="btn btn-secondary mt-3">← 返回後台</a>
</div>

<!-- Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">估價單詳細資訊</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>會員姓名：</strong><span id="modalUser"></span></p>
        <p><strong>車牌號碼：</strong><span id="modalPlate"></span></p>
        <p><strong>估價內容：</strong><br><span id="modalItems"></span></p>
        <p><strong>總金額：</strong>NT$ <span id="modalTotal"></span></p>
        <p><strong>狀態：</strong><span id="modalStatus"></span></p>
        <p><strong>建立時間：</strong><span id="modalTime"></span></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
      </div>
    </div>
  </div>
</div>

<script>
function showDetailModal(user, plate, items, total, status, time) {
  document.getElementById('modalUser').innerText = user;
  document.getElementById('modalPlate').innerText = plate;
  document.getElementById('modalItems').innerHTML = items;
  document.getElementById('modalTotal').innerText = total;
  document.getElementById('modalTime').innerText = time;

  let statusText = {
    'pending': '<span class="badge bg-warning text-dark">待確認</span>',
    'confirmed': '<span class="badge bg-success">已確認</span>',
    'cancelled': '<span class="badge bg-danger">已取消</span>'
  };
  document.getElementById('modalStatus').innerHTML = statusText[status] || '-';

  const modal = new bootstrap.Modal(document.getElementById('detailModal'));
  modal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
