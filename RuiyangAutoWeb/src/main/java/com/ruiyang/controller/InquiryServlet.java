package com.ruiyang.controller;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import com.ruiyang.dao.InquiryDao;
import com.ruiyang.model.Inquiry;
import com.ruiyang.util.DBUtil; // 假設你有 DBUtil 類別來管理資料庫連接

import javax.servlet.annotation.WebServlet; // 改為 javax.servlet 以兼容 Java SE 11
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

@SuppressWarnings("serial")
@WebServlet("/submitInquiry")
public class InquiryServlet extends HttpServlet {
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        int userId = Integer.parseInt(request.getParameter("userId"));
        String productName = request.getParameter("productName");
        int quantity = Integer.parseInt(request.getParameter("quantity"));

        try (Connection conn = DBUtil.getConnection()) { // 使用 DBUtil 類別的 getConnection 方法
            InquiryDao inquiryDao = new InquiryDao(conn);
            Inquiry inquiry = new Inquiry(0, userId, productName, quantity, null, "submitted");
            if (inquiryDao.createInquiry(inquiry)) {
                response.sendRedirect("inquiries.jsp");
            } else {
                request.setAttribute("errorMessage", "提交詢價失敗，請稍後再試");
                request.getRequestDispatcher("inquiry_form.jsp").forward(request, response);
            }
        } catch (SQLException e) {
            e.printStackTrace();
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("inquiry_form.jsp").forward(request, response);
        }
    }
}
