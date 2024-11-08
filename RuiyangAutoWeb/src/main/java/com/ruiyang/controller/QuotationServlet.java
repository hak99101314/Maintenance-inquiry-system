package com.ruiyang.controller;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.time.LocalDate;

import com.ruiyang.dao.QuotationDao;
import com.ruiyang.model.Quotation;
import com.ruiyang.util.DBUtil;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@WebServlet("/submitQuotation")
public class QuotationServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        try {
            // 取得請求參數並轉換數據類型
            System.out.println("正在獲取詢價單 ID、報價金額、員工 ID...");
            int inquiryId = Integer.parseInt(request.getParameter("inquiryId"));
            double quotedAmount = Double.parseDouble(request.getParameter("quotedAmount"));
            int employeeId = Integer.parseInt(request.getParameter("employeeId"));
            System.out.println("成功獲取請求參數");

            // 建立資料庫連線
            System.out.println("正在建立資料庫連線...");
            try (Connection conn = DBUtil.getConnection()) {
                System.out.println("資料庫連線成功");

                // 創建 QuotationDao 物件並新增報價
                QuotationDao quotationDao = new QuotationDao();
                // 使用工廠方法創建 Quotation 對象
                Quotation quotation = Quotation.fromLocalDate(0, inquiryId, quotedAmount, employeeId, LocalDate.now());
                System.out.println("正在創建報價資料...");

                if (quotationDao.createQuotation(quotation)) {
                    System.out.println("報價成功，重定向到 quotations.jsp");
                    response.sendRedirect("quotations.jsp");
                } else {
                    System.out.println("報價失敗，轉發到 quotation_form.jsp");
                    request.setAttribute("errorMessage", "提交報價失敗，請稍後再試");
                    request.getRequestDispatcher("quotation_form.jsp").forward(request, response);
                }
            } catch (SQLException e) {
                System.err.println("資料庫操作發生錯誤：" + e.getMessage());
                request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
                request.getRequestDispatcher("quotation_form.jsp").forward(request, response);
            }
        } catch (NumberFormatException e) {
            System.err.println("參數格式錯誤：" + e.getMessage());
            request.setAttribute("errorMessage", "參數格式錯誤，請檢查輸入");
            request.getRequestDispatcher("quotation_form.jsp").forward(request, response);
        } catch (Exception e) {
            System.err.println("未知錯誤：" + e.getMessage());
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("quotation_form.jsp").forward(request, response);
        }
    }
}
