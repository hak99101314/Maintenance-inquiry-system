<?php
define('DB_HOST', 'localhost');     // 資料庫主機
define('DB_USER', 'your_username'); // 資料庫用戶名
define('DB_PASS', 'your_password'); // 資料庫密碼
define('DB_NAME', 'repair_system'); // 資料庫名稱

function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        error_log("資料庫連接錯誤: " . $e->getMessage());
        return null;
    }
}
?> 