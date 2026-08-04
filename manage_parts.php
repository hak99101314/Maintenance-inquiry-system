<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$dbname = "睿煬企業社";
$username = "root";
$password = "karry,roy,jackson";
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

$message = "";

// 執行刪除操作
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // 刪除圖片
    $img_query = $conn->query("SELECT image FROM parts WHERE part_id = $delete_id");
    $img_row = $img_query->fetch_assoc();
    if ($img_row && !empty($img_row['image'])) {
        $img_path = 'assets/image/' . $img_row['image'];
        if (file_exists($img_path)) {
            unlink($img_path);
        }
    }

    $conn->query("DELETE FROM parts WHERE part_id = $delete_id");
    $message = "✅ 零件已成功刪除！";
}

// 查詢所有零件
$result = $conn->query("SELECT * FROM parts ORDER BY name ASC");
$parts = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>零件管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4"><i class="fas fa-tools"></i> 維修零件管理</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table class="table table-bordered table-hover bg-white shadow">
        <thead class="table-dark">
            <tr>
                <th>圖片</th>
                <th>零件名稱</th>
                <th>價格</th>
                <th>說明</th>
                <th style="width: 150px;">操作</th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($parts as $part): ?>
    <tr>
        <td>
            <?php
            $imageFile = !empty($part['image']) && file_exists('assets/image/' . $part['image']) ? $part['image'] : 'no-image.png';
            ?>
            <img src="assets/image/<?= htmlspecialchars($imageFile) ?>" width="80" height="80" class="img-thumbnail">
        </td>
        <td><?= htmlspecialchars($part['name']) ?></td>
        <td>NT$ <?= number_format($part['price']) ?></td>
        <td><?= htmlspecialchars($part['description']) ?></td>
        <td>
            <a href="edit_part.php?part_id=<?= intval($part['part_id']) ?>" class="btn btn-sm btn-warning mb-1">編輯</a>
            <a href="?delete_id=<?= intval($part['part_id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('確定要刪除這個零件嗎？')">刪除</a>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>

    </table>

    <a href="<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'staff_dashboard.php' ?>" class="btn btn-secondary mt-3">← 返回主畫面</a>
</div>

<!-- 引入 FontAwesome（如需使用圖示） -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
