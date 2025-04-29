<?php
// ========== 會話管理與安全驗證 ==========
session_start();  // 啟動會話管理機制

// 檢查用戶是否已登入，未登入則顯示錯誤訊息
if (!isset($_SESSION['user_id'])) {
    die("<p style='color:red;'>請先登入。</p>");
}

// 取得目前登入的使用者 ID 並轉換為整數，防止 SQL 注入
$user_id = intval($_SESSION['user_id']);

// ========== 資料庫連線設定 ==========
// 建立資料庫連線
$conn = new mysqli("localhost", "root", "karry,roy,jackson", "睿煬企業社");

// 檢查資料庫連線是否成功
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// ========== 資料查詢 ==========
// 查詢該使用者的所有預約記錄，按日期降序排序
$sql = "SELECT appointment_id, appointment_date, appointment_time, status 
        FROM appointments 
        WHERE customer_id = ? 
        ORDER BY appointment_date DESC";

// 準備 SQL 語句，防止 SQL 注入
$stmt = $conn->prepare($sql);

if ($stmt) {
    // 綁定參數並執行查詢
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // 顯示頁面標題
    echo "<h2>請選擇一筆預約記錄查看詳情</h2>";

    // 檢查是否有預約記錄
    if ($result->num_rows > 0) {
        // 開始建立預約列表
        echo "<ul>";
        // 遍歷所有預約記錄
        while ($row = $result->fetch_assoc()) {
            // 顯示每筆預約的連結和資訊
            echo "<li>
                    <a href='appointment_details.php?appointment_id=" . $row['appointment_id'] . "'>
                        預約日期: " . htmlspecialchars($row['appointment_date']) . " 
                        時間: " . htmlspecialchars($row['appointment_time']) . " 
                        狀態: " . htmlspecialchars($row['status']) . "
                    </a>
                  </li>";
        }
        echo "</ul>";
    } else {
        // 如果沒有預約記錄，顯示提示訊息
        echo "<p style='color:red;'>您沒有任何預約記錄。</p>";
    }

    // 關閉預處理語句
    $stmt->close();
} else {
    // 如果預處理語句準備失敗
    echo "<p style='color:red;'>查詢失敗，請聯繫管理員。</p>";
}

// ========== 資源釋放 ==========
// 關閉資料庫連線
$conn->close();
?>
