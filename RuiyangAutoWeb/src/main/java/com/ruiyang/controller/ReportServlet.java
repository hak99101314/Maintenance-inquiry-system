package com.ruiyang.controller;

import com.google.gson.Gson;
import com.ruiyang.dao.ReportDao;
import com.ruiyang.model.ReportData;
import com.ruiyang.util.DBUtil;

import javax.servlet.annotation.WebServlet;
import javax.servlet.ServletException;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

@WebServlet("/report")
public class ReportServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String reportType = request.getParameter("type");

        try (Connection conn = DBUtil.getConnection()) {
            ReportDao reportDao = new ReportDao(conn);
            List<ReportData> reportData;

            switch (reportType) {
                case "inquiry":
                    reportData = reportDao.getInquiryReportData();
                    break;
                case "order":
                    reportData = reportDao.getOrderReportData();
                    break;
                case "inventory":
                    reportData = reportDao.getInventoryReportData();
                    break;
                default:
                    reportData = new ArrayList<>();
            }

            String json = new Gson().toJson(reportData);
            response.setContentType("application/json");
            response.setCharacterEncoding("UTF-8");
            response.getWriter().write(json);
        } catch (SQLException e) {
            e.printStackTrace();
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "系統錯誤，請稍後再試");
        }
    }
}
