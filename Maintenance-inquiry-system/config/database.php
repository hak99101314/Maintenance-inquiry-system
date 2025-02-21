<?php
define('DB_HOST', 'localhost');     // 確認主機名稱
define('DB_USER', 'root');         // 確認用戶名
define('DB_PASS', '');             // 確認密碼
define('DB_NAME', 'repair_system'); // 確認資料庫名稱

function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $conn;
    } catch(PDOException $e) {
        error_log("資料庫連接錯誤: " . $e->getMessage());
        throw new Exception("資料庫連接錯誤: " . $e->getMessage());
    }
}
?> 