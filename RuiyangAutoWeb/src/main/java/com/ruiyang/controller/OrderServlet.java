package com.ruiyang.controller;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import com.ruiyang.dao.OrderDao;
import com.ruiyang.model.Order;
import com.ruiyang.util.DBUtil; // 假設你有一個 DBUtil 類來管理資料庫連接

import javax.servlet.annotation.WebServlet; // 使用 javax.servlet 以兼容 Java SE 11
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

@WebServlet("/createOrder")
public class OrderServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        int userId = Integer.parseInt(request.getParameter("userId"));
        int quotationId = Integer.parseInt(request.getParameter("quotationId"));

        try (Connection conn = DBUtil.getConnection()) {
            OrderDao orderDao = new OrderDao(conn);
            Order order = new Order(0, userId, quotationId, null, "待維修");
            if (orderDao.createOrder(order)) {
                response.sendRedirect("orders.jsp");
            } else {
                request.setAttribute("errorMessage", "生成訂單失敗，請稍後再試");
                request.getRequestDispatcher("quotation.jsp").forward(request, response);
            }
        } catch (SQLException e) {
            e.printStackTrace();
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("quotation.jsp").forward(request, response);
        }
    }
}
