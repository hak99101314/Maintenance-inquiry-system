<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查詢維修紀錄</title>
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

        .btn-primary {
            background-color: #66ccff;
            border-color: #66ccff;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #4da6ff;
            border-color: #4da6ff;
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
                    <li class="nav-item"><a class="nav-link" href="index.jsp">首頁</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.jsp">登入</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.jsp">註冊</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="query.jsp">查詢維修紀錄</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-4">查詢維修紀錄</h2>
        <form action="query.html" method="post">
            <div class="mb-3">
                <label for="plate_number" class="form-label">車牌號碼</label>
                <input type="text" class="form-control" id="plate_number" name="plate_number" required>
            </div>
            <div class="mb-3">
                <label for="phone_number" class="form-label">電話號碼</label>
                <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">查詢維修紀錄</button>
        </form>
        <div id="query-result" class="mt-4">
            <c:if test="${not empty repairRecords}">
                <h4>維修紀錄:</h4>
                <ul>
                    <c:forEach var="record" items="${repairRecords}">
                        <li>維修日期: ${record.repairDate}, 維修項目: ${record.repairItem}</li>
                    </c:forEach>
                </ul>
            </c:if>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
