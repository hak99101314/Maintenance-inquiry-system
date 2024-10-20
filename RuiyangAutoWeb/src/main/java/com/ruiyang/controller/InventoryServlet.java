package com.ruiyang.controller;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import com.ruiyang.dao.InventoryDao;
import com.ruiyang.util.DBUtil; // 假設你有一個 DBUtil 類來管理資料庫連接

import javax.servlet.annotation.WebServlet; // 使用 javax.servlet 以兼容 Java SE 11
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

@WebServlet("/updateInventory")
public class InventoryServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String partName = request.getParameter("partName");
        int quantity = Integer.parseInt(request.getParameter("quantity"));

        try (Connection conn = DBUtil.getConnection()) { // 使用 DBUtil 類來獲取連接
            InventoryDao inventoryDao = new InventoryDao(conn);
            if (inventoryDao.updateInventory(partName, quantity)) {
                response.sendRedirect("inventory.jsp");
            } else {
                request.setAttribute("errorMessage", "更新庫存失敗，請稍後再試");
                request.getRequestDispatcher("inventory_update.jsp").forward(request, response);
            }
        } catch (SQLException e) {
            e.printStackTrace();
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("inventory_update.jsp").forward(request, response);
        }
    }
}
