<?php
// 連接資料庫
$servername = "localhost";
$username = "root";
$password = 'karry,roy,jackson';
$dbname = "睿煬企業社";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>系統資訊</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>系統資訊</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>資訊項目</th>
                    <th>值</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>伺服器名稱</td>
                    <td><?php echo htmlspecialchars($_SERVER['SERVER_NAME']); ?></td>
                </tr>
                <tr>
                    <td>PHP 版本</td>
                    <td><?php echo htmlspecialchars(PHP_VERSION); ?></td>
                </tr>
                <tr>
                    <td>資料庫版本</td>
                    <td><?php echo htmlspecialchars($conn->server_info); ?></td>
                </tr>
                <tr>
                    <td>當前時間</td>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
