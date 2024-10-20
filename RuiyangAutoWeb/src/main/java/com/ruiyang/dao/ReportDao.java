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

    // 查詢詢價報表數據
    public List<ReportData> getInquiryReportData() throws SQLException {
        String sql = "SELECT product_name, COUNT(*) AS inquiry_count FROM inquiries GROUP BY product_name";
        List<ReportData> data = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            while (resultSet.next()) {
                ReportData reportData = new ReportData(
                        resultSet.getString("product_name"),
                        resultSet.getInt("inquiry_count")
                );
                data.add(reportData);
            }
        }
        return data;
    }

    // 查詢訂單報表數據
    public List<ReportData> getOrderReportData() throws SQLException {
        String sql = "SELECT status, COUNT(*) AS order_count FROM orders GROUP BY status";
        List<ReportData> data = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            while (resultSet.next()) {
                ReportData reportData = new ReportData(
                        resultSet.getString("status"),
                        resultSet.getInt("order_count")
                );
                data.add(reportData);
            }
        }
        return data;
    }

    // 查詢庫存報表數據
    public List<ReportData> getInventoryReportData() throws SQLException {
        String sql = "SELECT part_name, quantity FROM inventory";
        List<ReportData> data = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            while (resultSet.next()) {
                ReportData reportData = new ReportData(
                        resultSet.getString("part_name"),
                        resultSet.getInt("quantity")
                );
                data.add(reportData);
            }
        }
        return data;
    }
}
