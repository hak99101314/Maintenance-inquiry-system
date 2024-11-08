package com.ruiyang.dao;

import com.ruiyang.model.ReportData;
import com.ruiyang.util.DBUtil;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

public class ReportDao {

    public ReportDao(Connection conn) {
		// TODO Auto-generated constructor stub
	}

	// 獲取詢價報表數據
    public List<ReportData> getInquiryReportData() throws SQLException {
        System.out.println("開始查詢詢價報表數據...");
        String sql = "SELECT product_name, COUNT(*) AS inquiry_count FROM inquiries GROUP BY product_name";
        List<ReportData> data = new ArrayList<>();
        
        try (Connection connection = DBUtil.getConnection()) {
            System.out.println("資料庫連接成功");
            try (PreparedStatement statement = connection.prepareStatement(sql)) {
                System.out.println("SQL 語句已準備: " + sql);
                try (ResultSet resultSet = statement.executeQuery()) {
                    System.out.println("SQL 查詢執行成功");
                    
                    while (resultSet.next()) {
                        ReportData reportData = new ReportData(
                                resultSet.getString("product_name"),
                                resultSet.getInt("inquiry_count")
                        );
                        data.add(reportData);
                    }
                    System.out.println("資料處理完成，查詢結果已存儲");
                }
            }
        } catch (SQLException e) {
            System.err.println("查詢詢價報表數據時發生錯誤: " + e.getMessage());
            throw e;
        }
        System.out.println("詢價報表數據查詢完成");
        return data;
    }

    // 獲取訂單報表數據
    public List<ReportData> getOrderReportData() throws SQLException {
        System.out.println("開始查詢訂單報表數據...");
        String sql = "SELECT status, COUNT(*) AS order_count FROM orders GROUP BY status";
        List<ReportData> data = new ArrayList<>();
        
        try (Connection connection = DBUtil.getConnection()) {
            System.out.println("資料庫連接成功");
            try (PreparedStatement statement = connection.prepareStatement(sql)) {
                System.out.println("SQL 語句已準備: " + sql);
                try (ResultSet resultSet = statement.executeQuery()) {
                    System.out.println("SQL 查詢執行成功");
                    
                    while (resultSet.next()) {
                        ReportData reportData = new ReportData(
                                resultSet.getString("status"),
                                resultSet.getInt("order_count")
                        );
                        data.add(reportData);
                    }
                    System.out.println("資料處理完成，查詢結果已存儲");
                }
            }
        } catch (SQLException e) {
            System.err.println("查詢訂單報表數據時發生錯誤: " + e.getMessage());
            throw e;
        }
        System.out.println("訂單報表數據查詢完成");
        return data;
    }

    // 獲取庫存報表數據
    public List<ReportData> getInventoryReportData() throws SQLException {
        System.out.println("開始查詢庫存報表數據...");
        String sql = "SELECT part_name, quantity FROM inventory";
        List<ReportData> data = new ArrayList<>();
        
        try (Connection connection = DBUtil.getConnection()) {
            System.out.println("資料庫連接成功");
            try (PreparedStatement statement = connection.prepareStatement(sql)) {
                System.out.println("SQL 語句已準備: " + sql);
                try (ResultSet resultSet = statement.executeQuery()) {
                    System.out.println("SQL 查詢執行成功");
                    
                    while (resultSet.next()) {
                        ReportData reportData = new ReportData(
                                resultSet.getString("part_name"),
                                resultSet.getInt("quantity")
                        );
                        data.add(reportData);
                    }
                    System.out.println("資料處理完成，查詢結果已存儲");
                }
            }
        } catch (SQLException e) {
            System.err.println("查詢庫存報表數據時發生錯誤: " + e.getMessage());
            throw e;
        }
        System.out.println("庫存報表數據查詢完成");
        return data;
    }
}
