// Start Generation Here
<?php
// 檢查是否有提交的預約取消請求
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 獲取預約ID
    $appointment_id = $_POST['appointment_id'];

    // 連接到數據庫
    $conn = new mysqli("localhost", "root", "", "睿煬企業社");

    // 檢查連接
    if ($conn->connect_error) {
        die("連接失敗: " . $conn->connect_error);
    }

    // 準備刪除預約的SQL語句
    $sql = "DELETE FROM appointments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $appointment_id);

    // 執行語句並檢查結果
    if ($stmt->execute()) {
        echo "預約已成功取消。";
    } else {
        echo "取消預約時出錯: " . $stmt->error;
    }

    // 關閉連接
    $stmt->close();
    $conn->close();
}
?>
<form method="post" action="">
    <label for="appointment_id">預約ID:</label>
    <input type="text" id="appointment_id" name="appointment_id" required>
    <input type="submit" value="取消預約">
</form>
