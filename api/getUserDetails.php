<?php
// get_user.php
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
$sql = "SELECT user_id, username, full_name, email, role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => '查無此用戶']);
}
$stmt->close();
$conn->close();
?>
