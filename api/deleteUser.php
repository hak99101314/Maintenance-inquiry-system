<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => '未登入']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => '缺少用戶ID']);
    exit();
}

$servername  = "localhost";
$dbUsername  = "root";
$dbPassword  = "karry,roy,jackson";
$dbName      = "睿煬企業社";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => "資料庫連線失敗: " . $conn->connect_error]);
    exit();
}

$user_id = intval($_GET['id']);
$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => "刪除失敗: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>
