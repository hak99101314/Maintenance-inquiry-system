<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員註冊</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: '微軟正黑體', sans-serif;
            background-color: #f8f8f8;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23d1e8ff' fill-opacity='0.4'%3E%3Cpath d='M0 38.59l2.83-2.83 1.41 1.41L1.41 40H0v-1.41zM0 1.4l2.83 2.83 1.41-1.41L1.41 0H0v1.41zM38.59 40l-2.83-2.83 1.41-1.41L40 38.59V40h-1.41zM40 1.41l-2.83 2.83-1.41-1.41L38.59 0H40v1.41zM20 18.6l2.83-2.83 1.41 1.41L21.41 20l2.83 2.83-1.41 1.41L20 21.41l-2.83 2.83-1.41-1.41L18.59 20l-2.83-2.83 1.41-1.41L20 18.59z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .navbar {
            background-color: #66ccff;
        }

        .navbar-brand, .nav-link {
            color: white;
            font-weight: bold;
        }

        .card {
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .form-control {
            border-radius: 20px;
            border-color: #ced4da;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #66ccff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.25);
        }

        .btn-primary, .btn-outline-secondary {
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #4da6ff;
            border-color: #4da6ff;
            transform: translateY(-2px);
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: #ffffff;
            border-color: #6c757d;
            transform: translateY(-2px);
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }

        label {
            font-weight: bold;
            color: #333;
        }

        h2 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 2rem;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">維修系統</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.html">首頁</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.html">登入</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="register.html">註冊</a></li>
                    <li class="nav-item"><a class="nav-link" href="query.html">查詢維修紀錄</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-4">
                    <div class="card-body">
                        <div id="step1">
                            <h2 class="card-title text-center mb-4">會員註冊 - 步驟 1</h2>
                            <form id="step1-form">
                                <div class="mb-4">
                                    <label for="username" class="form-label">帳號:</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-4">
                                    <label for="email" class="form-label">電子郵件:</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">下一步</button>
                                </div>
                            </form>
                        </div>

                        <div id="step2" class="hidden">
                            <h2 class="card-title text-center mb-4">會員註冊 - 步驟 2</h2>
                            <form id="step2-form">
                                <div class="mb-4">
                                    <label for="password" class="form-label">密碼:</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">確認密碼:</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">下一步</button>
                                </div>
                            </form>
                        </div>

                        <div id="step3" class="hidden">
                            <h2 class="card-title text-center mb-4">會員註冊 - 步驟 3</h2>
                            <form id="step3-form">
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
                            </form>
                        </div>

                        <div id="loading" class="text-center mt-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('step1-form').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
        });

        document.getElementById('step2-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                alert('密碼與確認密碼不一致');
                return;
            }
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step3').classList.remove('hidden');
        });

        document.getElementById('step3-form').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('loading').style.display = 'block';
            // 在這裡處理表單提交，例如使用 AJAX 發送數據
        });

        function showTerms() {
            alert('服務條款: 我們提供的服務是免費的，請遵守相關法律法規。');
        }

        function showPrivacy() {
            alert('隱私政策: 我們將妥善保護您的個人隱私，信息不會被洩露。');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
