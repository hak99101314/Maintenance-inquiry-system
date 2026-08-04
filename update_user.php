<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id   = intval($_POST['user_id']);
    $full_name = $_POST['full_name'];
    $email     = $_POST['email'];
    $role      = $_POST['role'];
    $password  = isset($_POST['password']) ? $_POST['password'] : '';

    $servername  = "localhost";
    $dbUsername  = "root";
    $dbPassword  = "karry,roy,jackson";
    $dbName      = "睿煬企業社";

    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => "資料庫連線失敗: " . $conn->connect_error]);
        exit();
    }

    // 建立更新 SQL
    if (!empty($password)) {
        // 密碼需加密（例如使用 password_hash()）
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET full_name = ?, email = ?, role = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $full_name, $email, $role, $hashedPassword, $user_id);
    } else {
        $sql = "UPDATE users SET full_name = ?, email = ?, role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $full_name, $email, $role, $user_id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => "更新失敗: " . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
}
?>
