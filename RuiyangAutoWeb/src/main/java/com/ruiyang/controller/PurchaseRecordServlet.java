package com.ruiyang.controller;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import com.ruiyang.dao.PurchaseRecordDao;
import com.ruiyang.model.PurchaseRecord;
import com.ruiyang.util.DBUtil;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@WebServlet("/createPurchaseRecord")
public class PurchaseRecordServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        System.out.println("收到POST請求，開始處理");

        // 從前端表單中獲取輸入的參數
        String partName = request.getParameter("partName");
        int quantity;
        double price;

        try {
            quantity = Integer.parseInt(request.getParameter("quantity"));
            price = Double.parseDouble(request.getParameter("price"));
        } catch (NumberFormatException e) {
            System.out.println("數量或價格格式錯誤：" + e.getMessage());
            request.setAttribute("errorMessage", "數量或價格格式錯誤");
            request.getRequestDispatcher("purchase_form.jsp").forward(request, response);
            return;
        }

        String supplier = request.getParameter("supplier");
        System.out.println("取得參數：partName=" + partName + ", quantity=" + quantity + ", supplier=" + supplier + ", price=" + price);

        try (Connection conn = DBUtil.getConnection()) {
            System.out.println("資料庫連接成功");

            // 建立資料存取物件 (DAO)，以進行資料庫操作
            PurchaseRecordDao purchaseDao = new PurchaseRecordDao();

            // 建立一個新的進貨記錄物件
            PurchaseRecord record = new PurchaseRecord(0, partName, quantity, supplier, null, price);

            // 嘗試將進貨記錄儲存至資料庫中
            if (purchaseDao.createPurchaseRecord(record)) {
                System.out.println("新增進貨記錄成功");
                response.sendRedirect("purchase_records.jsp");
            } else {
                System.out.println("新增進貨記錄失敗");
                request.setAttribute("errorMessage", "新增進貨記錄失敗，請稍後再試");
                request.getRequestDispatcher("purchase_form.jsp").forward(request, response);
            }
        } catch (SQLException e) {
            System.out.println("資料庫錯誤：" + e.getMessage());
            e.printStackTrace();
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("purchase_form.jsp").forward(request, response);
        }
    }
}
