<?php
session_start();

$response = ["success" => false, "message" => ""];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $dbUsername = "root";
    $dbPassword = "";
    $dbName = "睿煬企業社";

    $conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
    if ($conn->connect_error) {
        $response["message"] = "資料庫連線失敗：" . $conn->connect_error;
        echo json_encode($response);
        exit();
    }

    $username             = $conn->real_escape_string($_POST['username'] ?? '');
    $email                = $conn->real_escape_string($_POST['email'] ?? '');
    $password             = $_POST['password'] ?? '';
    $confirm_password     = $_POST['confirm_password'] ?? '';
    $car_make             = $conn->real_escape_string($_POST['car_make'] ?? '');
    $plate_number         = $conn->real_escape_string($_POST['plate_number'] ?? '');
    $engine_number        = $conn->real_escape_string($_POST['engine_number'] ?? '');
    $year_of_manufacture  = intval($_POST['year_of_manufacture'] ?? 0);
    $month_of_manufacture = intval($_POST['month_of_manufacture'] ?? 0);
    $full_name            = $conn->real_escape_string($_POST['full_name'] ?? '');
    $contact_number       = $conn->real_escape_string($_POST['contact_number'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($full_name)) {
        $response["message"] = "請填寫所有必要欄位";
        echo json_encode($response);
        exit();
    }

    if ($password !== $confirm_password) {
        $response["message"] = "密碼與確認密碼不一致";
        echo json_encode($response);
        exit();
    }

    $checkQuery = "SELECT user_id FROM users WHERE username='$username'";
    $checkResult = $conn->query($checkQuery);
    if ($checkResult && $checkResult->num_rows > 0) {
        $response["message"] = "此帳號已存在，請更換帳號";
        echo json_encode($response);
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $insertUser = "INSERT INTO users (username, password_hash, role, full_name, contact_number, email) 
                   VALUES ('$username', '$password_hash', 'customer', '$full_name', '$contact_number', '$email')";

    if ($conn->query($insertUser) === TRUE) {
        $new_user_id = $conn->insert_id;

        $insertVehicle = "INSERT INTO vehicles (license_plate, owner_id, brand, engine_number, year, month) 
                          VALUES ('$plate_number', '$new_user_id', '$car_make', '$engine_number', '$year_of_manufacture', '$month_of_manufacture')";

        if ($conn->query($insertVehicle) === TRUE) {
            // ✅ 新增成功，寄出通知信
            require_once 'send_email.php';

            $subject = "會員註冊成功通知 - 睿煬企業社";
            $body = "
    <div style='font-family:Arial,sans-serif; color:#333; background:#f9f9f9; padding:20px; border-radius:8px; max-width:600px; margin:auto;'>
        <h2 style='color:#2c3e50;'>親愛的 {$full_name} 您好</h2>
        <p>感謝您註冊成為睿煬企業社的會員，您的帳號已成功建立！</p>

        <p>以下是您的註冊資訊：</p>
        <ul>
            <li>帳號：{$username}</li>
            <li>車牌號碼：{$plate_number}</li>
            <li>聯絡電話：{$contact_number}</li>
        </ul>

        <p>歡迎使用我們的線上預約與查詢服務，期待為您提供最好的服務。</p>

        <p style='margin-top:30px;'>睿煬企業社 敬上</p>
    </div>
";

// 呼叫寄信 (改成正確的4個參數)
sendEmail($email, $full_name, $subject, $body);


            $response["success"] = true;
            $response["message"] = "註冊成功！";
        } else {
            $response["message"] = "車輛資料新增失敗：" . $conn->error;
        }
    } else {
        $response["message"] = "用戶資料新增失敗：" . $conn->error;
    }

    $conn->close();
    echo json_encode($response);
    exit();
}
?>


<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員註冊</title>
    <!-- 引入 Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- 引入 Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- 使用與 dashboard.php 相同的樣式 -->
    <style>
        /* 全局基本樣式 */
        body {
            font-family: 'Noto Sans TC', sans-serif;
            background: linear-gradient(45deg, #1a2980, #26d0ce);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            color: #333;
        }
        /* 導覽列 */
        .navbar {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        /* container 間距 */
        .container {
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        /* 卡片樣式 */
        .card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border: none;
        }
        /* 隱藏元素 */
        .hidden {
            display: none;
        }
        /* 修改表單輸入框樣式 */
        .form-control {
            border-radius: 10px;
            border: 1px solid #ccc;
            padding: 0.75rem;
        }
        .form-control:focus {
            border-color: #1a2980;
            box-shadow: 0 0 0 0.2rem rgba(26, 41, 128, 0.25);
        }
        /* 按鈕樣式調整 */
        .btn-primary {
            background-color: #1a2980;
            border-color: #1a2980;
            border-radius: 10px;
            font-weight: 500;
            padding: 0.65rem 1rem;
        }
        .btn-primary:hover {
            background-color: #15306b;
            border-color: #15306b;
        }
        /* Loading spinner 大小 */
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>

<body>
    <!-- 導覽列 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">維修查詢系統</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="login.php">登入</a></li>
                    <li class="nav-item"><a class="nav-link active" href="register.php">註冊</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- 主內容 -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-4">
                    <div class="card-body">
                        <!-- 若有錯誤訊息，顯示在此 -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger text-center">
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <!-- 多步驟註冊表單，所有欄位包在同一個表單中 -->
                        <form id="register-form" method="post" action="register.php">
                            <!-- 註冊步驟 1：帳號與電子信箱 -->
                            <div id="step1">
                                <h2 class="card-title text-center mb-4">會員註冊 - 步驟 1</h2>
                                <div class="mb-4">
                                    <label for="username" class="form-label">帳號:</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-4">
                                    <label for="email" class="form-label">電子信箱:</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep(1)">下一步</button>
                                </div>
                            </div>

                            <!-- 註冊步驟 2：密碼設定 -->
                            <div id="step2" class="hidden">
                                <h2 class="card-title text-center mb-4">會員註冊 - 步驟 2</h2>
                                <div class="mb-4">
                                    <label for="password" class="form-label">密碼:</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">確認密碼:</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep(2)">下一步</button>
                                </div>
                            </div>

                            <!-- 註冊步驟 3：車輛與個人資料 -->
                            <div id="step3" class="hidden">
                                <h2 class="card-title text-center mb-4">會員註冊 - 步驟 3</h2>
                                <div class="mb-4">
                                    <label for="car_make" class="form-label">車子廠牌:</label>
                                    <input type="text" class="form-control" id="car_make" name="car_make" required>
                                </div>
                                <div class="mb-4">
                                    <label for="plate_number" class="form-label">車身車牌號碼:</label>
                                    <input type="text" class="form-control" id="plate_number" name="plate_number" required>
                                </div>
                                <div class="mb-4">
                                    <label for="engine_number" class="form-label">車子引擎號:</label>
                                    <input type="text" class="form-control" id="engine_number" name="engine_number" required>
                                </div>
                                <div class="mb-4">
                                    <label for="year_of_manufacture" class="form-label">出場年份:</label>
                                    <input type="number" class="form-control" id="year_of_manufacture" name="year_of_manufacture" min="1900" max="2099" required>
                                </div>
                                <div class="mb-4">
                                    <label for="month_of_manufacture" class="form-label">出場月份:</label>
                                    <input type="number" class="form-control" id="month_of_manufacture" name="month_of_manufacture" min="1" max="12" required>
                                </div>
                                <div class="mb-4">
                                    <label for="full_name" class="form-label">姓名:</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>
                                <div class="mb-4">
                                    <label for="contact_number" class="form-label">聯絡電話:</label>
                                    <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        我同意<a href="#" onclick="showTerms()">服務條款</a>和<a href="#" onclick="showPrivacy()">隱私政策</a>
                                    </label>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">註冊</button>
                                </div>
                            </div>
                        </form>

                        <!-- 載入動畫（註冊送出時可顯示） -->
                        <div id="loading" class="text-center mt-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div><!-- card-body 結束 -->
                </div><!-- card 結束 -->
            </div><!-- col-md-8 結束 -->
        </div><!-- row 結束 -->
    </div><!-- container 結束 -->

    <!-- JavaScript 區塊，控制多步驟表單顯示與驗證 -->
    <script>
        // 切換步驟函式：傳入目前步驟數字
        function nextStep(currentStep) {
            if (currentStep === 1) {
                // 檢查步驟 1 欄位是否填寫
                var username = document.getElementById('username').value.trim();
                var email = document.getElementById('email').value.trim();
                if (username === "" || email === "") {
                    alert("請填寫帳號與電子信箱");
                    return;
                }
                // 切換到步驟 2
                document.getElementById('step1').classList.add('hidden');
                document.getElementById('step2').classList.remove('hidden');
            } else if (currentStep === 2) {
                // 檢查步驟 2 欄位
                var password = document.getElementById('password').value;
                var confirmPassword = document.getElementById('confirm_password').value;
                if (password === "" || confirmPassword === "") {
                    alert("請填寫密碼與確認密碼");
                    return;
                }
                if (password !== confirmPassword) {
                    alert("密碼與確認密碼不一致！");
                    return;
                }
                // 切換到步驟 3
                document.getElementById('step2').classList.add('hidden');
                document.getElementById('step3').classList.remove('hidden');
            }
        }

        // 顯示服務條款
        function showTerms() {
            alert("服務條款內容...");
        }
        // 顯示隱私政策
        function showPrivacy() {
            alert("隱私政策內容...");
        }
    </script>
    <!-- 引入 Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/registration.js"></script>
</body>
</html>
