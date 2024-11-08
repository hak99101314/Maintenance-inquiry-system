package com.ruiyang.controller;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import com.ruiyang.dao.OrderDao;
import com.ruiyang.model.Order;
import com.ruiyang.util.DBUtil; // 假設你有一個 DBUtil 類來管理資料庫連接

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@WebServlet("/createOrder") // 設定此 Servlet 的 URL 路徑為 "/createOrder"
public class OrderServlet extends HttpServlet {
    private static final long serialVersionUID = 1L; // 序列化 ID，用於檢查類的版本一致性

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        try {
            // 取得從前端傳遞過來的 "userId" 參數並將其轉換為整數
            int userId = Integer.parseInt(request.getParameter("userId"));
            System.out.println("取得 userId 成功: " + userId); // 列印 userId

            // 取得從前端傳遞過來的 "quotationId" 參數並將其轉換為整數
            int quotationId = Integer.parseInt(request.getParameter("quotationId"));
            System.out.println("取得 quotationId 成功: " + quotationId); // 列印 quotationId

            // 嘗試使用 DBUtil 類來取得資料庫連接，並自動在結束時關閉連接
            try (Connection conn = DBUtil.getConnection()) {
                System.out.println("資料庫連接成功"); // 列印資料庫連接狀態

                // 使用取得的連接來建立 OrderDao 物件
                OrderDao orderDao = new OrderDao();
                // 創建一個新的 Order 物件，初始狀態設為 "待維修"
                Order order = new Order(0, userId, quotationId, null, "待維修");
                System.out.println("創建訂單物件成功"); // 列印訂單物件創建狀態

                // 呼叫 orderDao 的 createOrder 方法來將訂單插入到資料庫
                if (orderDao.createOrder(order)) {
                    System.out.println("訂單創建成功"); // 列印訂單創建成功
                    // 如果訂單創建成功，重定向到 orders.jsp 頁面
                    response.sendRedirect("orders.jsp");
                } else {
                    System.out.println("訂單創建失敗"); // 列印訂單創建失敗
                    // 如果訂單創建失敗，設定錯誤訊息並轉發回 quotation.jsp 頁面
                    request.setAttribute("errorMessage", "生成訂單失敗，請稍後再試");
                    request.getRequestDispatcher("quotation.jsp").forward(request, response);
                }
            } catch (SQLException e) {
                // 如果資料庫操作發生錯誤，輸出錯誤日誌
                e.printStackTrace();
                System.out.println("資料庫操作錯誤"); // 列印資料庫錯誤
                // 設定系統錯誤訊息並轉發回 quotation.jsp 頁面
                request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
                request.getRequestDispatcher("quotation.jsp").forward(request, response);
            }
        } catch (NumberFormatException e) {
            System.out.println("參數格式錯誤: " + e.getMessage()); // 列印參數格式錯誤
            request.setAttribute("errorMessage", "無效的參數格式，請檢查輸入");
            request.getRequestDispatcher("quotation.jsp").forward(request, response);
        }
    }
}
