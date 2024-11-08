<%@ page language="java" contentType="text/html; charset=UTF-8"
    pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>訂單管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">訂單管理</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>訂單編號</th>
                    <th>會員編號</th>
                    <th>報價單編號</th>
                    <th>訂單日期</th>
                    <th>狀態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <c:forEach var="order" items="${orders}">
                    <tr>
                        <td>${order.orderId}</td>
                        <td>${order.userId}</td>
                        <td>${order.quotationId}</td>
                        <td>${order.orderDate}</td>
                        <td>${order.status}</td>
                        <td>
                            <form action="updateOrderStatus" method="post" class="d-inline">
                                <input type="hidden" name="orderId" value="${order.orderId}">
                                <select name="status" class="form-select form-select-sm d-inline w-auto">
                                    <option value="待維修" ${order.status == '待維修' ? 'selected' : ''}>待維修</option>
                                    <option value="維修中" ${order.status == '維修中' ? 'selected' : ''}>維修中</option>
                                    <option value="已完成" ${order.status == '已完成' ? 'selected' : ''}>已完成</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">更新</button>
                            </form>
                        </td>
                    </tr>
                </c:forEach>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
