package com.ruiyang.controller;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

import com.google.gson.Gson;
import com.ruiyang.dao.ReportDao;
import com.ruiyang.model.ReportData;
import com.ruiyang.util.DBUtil;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

@WebServlet("/report")
public class ReportServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        System.out.println("doGet method invoked.");

        // 取得報表類型參數
        String reportType = request.getParameter("type");
        System.out.println("Report type received: " + reportType);

        // 使用 try-with-resources 獲取資料庫連接，確保連接在使用後關閉
        try (Connection conn = DBUtil.getConnection()) {
            System.out.println("Database connection established.");

            // 創建 ReportDao 物件來查詢數據
            ReportDao reportDao = new ReportDao(conn);
            List<ReportData> reportData;

            // 根據報表類型選擇對應的查詢方法
            switch (reportType) {
                case "inquiry":
                    System.out.println("Fetching inquiry report data...");
                    reportData = reportDao.getInquiryReportData();
                    break;
                case "order":
                    System.out.println("Fetching order report data...");
                    reportData = reportDao.getOrderReportData();
                    break;
                case "inventory":
                    System.out.println("Fetching inventory report data...");
                    reportData = reportDao.getInventoryReportData();
                    break;
                default:
                    System.out.println("Invalid report type provided. Sending empty data.");
                    reportData = new ArrayList<>();
            }

            // 將查詢結果轉換為 JSON 格式
            String json = new Gson().toJson(reportData);
            System.out.println("Data successfully converted to JSON format.");

            // 設置回應屬性並寫入 JSON 資料
            response.setContentType("application/json");
            response.setCharacterEncoding("UTF-8");
            response.getWriter().write(json);
            System.out.println("JSON data sent in response.");

        } catch (SQLException e) {
            // 若發生錯誤，列印錯誤訊息
            System.err.println("Database error: " + e.getMessage());
            e.printStackTrace();
            response.sendError(HttpServletResponse.SC_INTERNAL_SERVER_ERROR, "System error, please try again later.");
        }

        System.out.println("doGet method execution finished.");
    }
}
