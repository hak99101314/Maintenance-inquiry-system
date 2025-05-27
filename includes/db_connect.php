<?php
// 資料庫連線設定
$servername = "localhost";
$username = "root";  // 預設的 XAMPP MySQL 用戶名
$password = "";      // 預設的 XAMPP MySQL 密碼
$dbname = "睿煬企業社";  // 您的資料庫名稱

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 設定字符集為 utf8mb4
$conn->set_charset("utf8mb4");

// 檢查連線
if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}

// 設定時區
date_default_timezone_set('Asia/Taipei');
?> 