<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "睿煬企業社");
if ($conn->connect_error) {
    die("連線失敗：" . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estimate_id = intval($_POST['estimate_id']);
    $action = $_POST['action'];

    if ($action === 'confirm') {
        $conn->query("UPDATE estimates SET status = 'confirmed' WHERE estimate_id = $estimate_id AND member_id = $user_id");
    } elseif ($action === 'cancel') {
        $conn->query("UPDATE estimates SET status = 'cancelled' WHERE estimate_id = $estimate_id AND member_id = $user_id");
    }
}

// 抓會員的車輛清單
$plates = $conn->query("SELECT license_plate FROM vehicles WHERE owner_id = $user_id");

// 取得篩選條件
$selected_plate = $_GET['plate'] ?? '';
$filter_sql = !empty($selected_plate) ? " AND license_plate = '" . $conn->real_escape_string($selected_plate) . "'" : '';

$result = $conn->query("SELECT * FROM estimates WHERE member_id = $user_id $filter_sql ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>我的估價單</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">🔎 我的估價單</h2>
    <form class="mb-4 d-flex align-items-center gap-3" method="GET">
    <label class="form-label fw-bold mb-0">車牌篩選：</label>
    <select name="plate" class="form-select w-auto" onchange="this.form.submit()">
        <option value="">全部車輛</option>
        <?php while ($p = $plates->fetch_assoc()): ?>
            <option value="<?= $p['license_plate'] ?>" <?= $selected_plate === $p['license_plate'] ? 'selected' : '' ?>>
                <?= $p['license_plate'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<?php if (!empty($selected_plate)): ?>
    <div class="mb-3">
        <span class="text-muted">目前顯示車輛：<strong><?= htmlspecialchars($selected_plate) ?></strong></span>
    </div>
<?php endif; ?>

    <table class="table table-bordered bg-white shadow-sm">
    <thead class="table-dark">
    <tr>
        <th>日期</th>
        <th>車牌號碼</th>
        <th>總金額 (NT$)</th>
        <th>狀態</th>
        <th>操作</th>
    </tr>
</thead>

    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
    <td><?= htmlspecialchars($row['license_plate']) ?></td>
    <td><?= number_format($row['total_price']) ?></td>
            <td>
                <?php
                $status = $row['status'] ?? 'pending';
                echo match ($status) {
                    'confirmed' => '<span class="badge bg-success">已確認</span>',
                    'cancelled' => '<span class="badge bg-danger">已取消</span>',
                    default => '<span class="badge bg-warning text-dark">待確認</span>'
                };
                ?>
            </td>
            <td>
                <button class="btn btn-sm btn-info text-white mb-1"
                        onclick="showEstimateModal(`<?= htmlspecialchars($row['created_at']) ?>`, `<?= htmlspecialchars($row['items']) ?>`, `<?= number_format($row['total_price']) ?>`, `<?= $status ?>`)">
                    🔍 查看內容
                </button>
                <?php if ($row['status'] === 'pending'): ?>
                    <form method="POST" class="d-flex gap-2 mt-1">
                        <input type="hidden" name="estimate_id" value="<?= $row['estimate_id'] ?>">
                        <button type="button" class="btn btn-sm btn-success" onclick="openModal('confirm', <?= $row['estimate_id'] ?>)">確認維修</button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="openModal('cancel', <?= $row['estimate_id'] ?>)">取消維修</button>
                    </form>
                <?php else: ?>
                    <span class="text-muted"></span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>


    <a href="dashboard.php" class="btn btn-secondary">← 返回會員首頁</a>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">確認動作</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalText">您確定要執行這個操作嗎？</div>
      <input type="hidden" name="estimate_id" id="modalEstimateId">
      <input type="hidden" name="action" id="modalAction">
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
        <button type="submit" class="btn btn-primary">確認</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(action, id) {
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    document.getElementById('modalEstimateId').value = id;
    document.getElementById('modalAction').value = action;

    const text = action === 'confirm' ? '您確定要進行維修嗎？' : '您確定要取消維修嗎？';
    document.getElementById('modalText').innerText = text;

    modal.show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- 詳細內容彈跳視窗 -->
<div class="modal fade" id="estimateDetailModal" tabindex="-1" aria-labelledby="estimateDetailLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h5 class="modal-title" id="estimateDetailLabel">📄 估價單內容</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>建立日期：</strong><span id="modalDate"></span></p>
        <p><strong>估價內容：</strong><br><span id="modalItems"></span></p>
        <p><strong>總金額：</strong>NT$ <span id="modalTotal"></span></p>
        <p><strong>目前狀態：</strong><span id="modalStatus"></span></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
      </div>
    </div>
  </div>
</div>

</body>
<script>
function showEstimateModal(date, items, total, status) {
    const statusText = {
        'pending': '<span class="badge bg-warning text-dark">待確認</span>',
        'confirmed': '<span class="badge bg-success">已確認</span>',
        'cancelled': '<span class="badge bg-danger">已取消</span>'
    };

    document.getElementById('modalDate').innerText = date;
    document.getElementById('modalItems').innerText = items;
    document.getElementById('modalTotal').innerText = total;
    document.getElementById('modalStatus').innerHTML = statusText[status] ?? '不明';

    const modal = new bootstrap.Modal(document.getElementById('estimateDetailModal'));
    modal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</html>
