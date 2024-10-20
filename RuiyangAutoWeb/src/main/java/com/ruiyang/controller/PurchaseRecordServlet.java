package com.ruiyang.controller;

import com.ruiyang.dao.PurchaseRecordDao;
import com.ruiyang.model.PurchaseRecord;
import com.ruiyang.util.DBUtil;

import javax.servlet.annotation.WebServlet;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

@WebServlet("/createPurchaseRecord")
public class PurchaseRecordServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String partName = request.getParameter("partName");
        int quantity = Integer.parseInt(request.getParameter("quantity"));
        String supplier = request.getParameter("supplier");
        double price = Double.parseDouble(request.getParameter("price"));

        try (Connection conn = DBUtil.getConnection()) {
            PurchaseRecordDao purchaseDao = new PurchaseRecordDao(conn);
            PurchaseRecord record = new PurchaseRecord(0, partName, quantity, supplier, null, price);
            if (purchaseDao.createPurchaseRecord(record)) {
                response.sendRedirect("purchase_records.jsp");
            } else {
                request.setAttribute("errorMessage", "新增進貨記錄失敗，請稍後再試");
                request.getRequestDispatcher("purchase_form.jsp").forward(request, response);
            }
        } catch (SQLException e) {
            e.printStackTrace();
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("purchase_form.jsp").forward(request, response);
        }
    }
}
