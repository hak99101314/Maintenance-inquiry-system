package com.ruiyang.controller;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import com.ruiyang.dao.InquiryDao;
import com.ruiyang.model.Inquiry;
import com.ruiyang.util.DBUtil;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@SuppressWarnings("serial")
@WebServlet("/submitInquiry")
public class InquiryServlet extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        System.out.println("接收到詢價提交請求");

        // 取得使用者提交的表單資料
        try {
            int userId = Integer.parseInt(request.getParameter("userId"));
            String productName = request.getParameter("productName");
            int quantity = Integer.parseInt(request.getParameter("quantity"));

            // 列印確認參數接收是否正確
            System.out.println("取得的 User ID: " + userId);
            System.out.println("取得的 Product Name: " + productName);
            System.out.println("取得的 Quantity: " + quantity);

            // 嘗試建立資料庫連接
            try (Connection conn = DBUtil.getConnection()) {
                System.out.println("成功連接資料庫");

                // 創建 InquiryDao 物件
                InquiryDao inquiryDao = new InquiryDao(conn);
                System.out.println("InquiryDao 物件創建成功");

                // 創建新的 Inquiry 物件，表示新的詢價單
                Inquiry inquiry = new Inquiry(0, userId, productName, quantity, null, "submitted");
                System.out.println("Inquiry 物件創建成功: " + inquiry);

                // 將詢價單寫入資料庫
                if (inquiryDao.createInquiry(inquiry)) {
                    System.out.println("詢價單提交成功");
                    response.sendRedirect("inquiries.jsp"); // 成功後重導向至詢價列表
                } else {
                    System.out.println("詢價單提交失敗");
                    request.setAttribute("errorMessage", "提交詢價失敗，請稍後再試");
                    request.getRequestDispatcher("inquiry_form.jsp").forward(request, response);
                }
            } catch (SQLException e) {
                System.out.println("資料庫操作異常");
                e.printStackTrace();
                request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
                request.getRequestDispatcher("inquiry_form.jsp").forward(request, response);
            }
        } catch (NumberFormatException e) {
            System.out.println("表單數據格式錯誤，請確認輸入數據格式");
            e.printStackTrace();
            request.setAttribute("errorMessage", "請確認表單輸入格式是否正確");
            request.getRequestDispatcher("inquiry_form.jsp").forward(request, response);
        }
    }
}
