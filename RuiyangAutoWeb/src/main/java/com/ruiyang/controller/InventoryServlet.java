package com.ruiyang.controller;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import com.ruiyang.dao.InventoryDao;
import com.ruiyang.util.DBUtil; // 假設你有一個 DBUtil 類來管理資料庫連接

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@WebServlet("/updateInventory")
public class InventoryServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        // 從請求中取得表單參數 partName 和 quantity
        String partName = request.getParameter("partName");
        int quantity = Integer.parseInt(request.getParameter("quantity"));
        
        // 列印輸入的 partName 和 quantity 確認是否正確取得
        System.out.println("取得的零件名稱: " + partName);
        System.out.println("取得的數量: " + quantity);

        try (Connection conn = DBUtil.getConnection()) { // 使用 DBUtil 類來獲取連接
            System.out.println("資料庫連接已建立");

            InventoryDao inventoryDao = new InventoryDao(conn); // 使用資料庫連接建立 InventoryDao 實例
            System.out.println("InventoryDao 實例已建立");

            // 嘗試更新庫存
            System.out.println("嘗試更新庫存...");
            if (inventoryDao.updateInventory(partName, quantity)) {
                System.out.println("庫存更新成功，重定向至 inventory.jsp 頁面");
                response.sendRedirect("inventory.jsp");
            } else {
                System.out.println("更新庫存失敗");
                request.setAttribute("errorMessage", "更新庫存失敗，請稍後再試");
                request.getRequestDispatcher("inventory_update.jsp").forward(request, response);
            }
        } catch (SQLException e) {
            System.out.println("資料庫操作發生錯誤：" + e.getMessage());
            e.printStackTrace(); // 打印錯誤堆疊追蹤以供調試

            // 設置錯誤訊息並轉發回 inventory_update.jsp 頁面
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("inventory_update.jsp").forward(request, response);
        }
    }
}
