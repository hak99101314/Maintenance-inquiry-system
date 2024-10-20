package com.ruiyang.controller;

import com.ruiyang.dao.QuotationDao;
import com.ruiyang.model.Quotation;
import com.ruiyang.util.DBUtil;

import javax.servlet.annotation.WebServlet;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

@WebServlet("/submitQuotation")
public class QuotationServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        int inquiryId = Integer.parseInt(request.getParameter("inquiryId"));
        double quotedAmount = Double.parseDouble(request.getParameter("quotedAmount"));
        int employeeId = Integer.parseInt(request.getParameter("employeeId"));

        try (Connection conn = DBUtil.getConnection()) {
            QuotationDao quotationDao = new QuotationDao(conn);
            Quotation quotation = new Quotation(0, inquiryId, quotedAmount, employeeId, null);
            if (quotationDao.createQuotation(quotation)) {
                response.sendRedirect("quotations.jsp");
            } else {
                request.setAttribute("errorMessage", "提交報價失敗，請稍後再試");
                request.getRequestDispatcher("quotation_form.jsp").forward(request, response);
            }
        } catch (SQLException e) {
            e.printStackTrace();
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("quotation_form.jsp").forward(request, response);
        }
    }
}
