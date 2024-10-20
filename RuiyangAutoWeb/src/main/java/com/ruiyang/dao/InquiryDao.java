package com.ruiyang.dao;

import com.ruiyang.model.Inquiry;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class InquiryDao {

    // 新增詢價單
    public boolean createInquiry(Inquiry inquiry) throws SQLException {
        String sql = "INSERT INTO inquiries (user_id, product_name, quantity, inquiry_date, status) VALUES (?, ?, ?, NOW(), 'submitted')";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, inquiry.getUserId());
            statement.setString(2, inquiry.getProductName());
            statement.setInt(3, inquiry.getQuantity());

            return statement.executeUpdate() > 0;
        }
    }

    // 查詢會員的所有詢價單
    public List<Inquiry> getInquiriesByUserId(int userId) throws SQLException {
        String sql = "SELECT * FROM inquiries WHERE user_id = ?";
        List<Inquiry> inquiries = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, userId);
            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    Inquiry inquiry = new Inquiry(
                            resultSet.getInt("inquiry_id"),
                            resultSet.getInt("user_id"),
                            resultSet.getString("product_name"),
                            resultSet.getInt("quantity"),
                            resultSet.getDate("inquiry_date"),
                            resultSet.getString("status")
                    );
                    inquiries.add(inquiry);
                }
            }
        }
        return inquiries;
    }

    // 查詢所有詢價單（供員工查看）
    public List<Inquiry> getAllInquiries() throws SQLException {
        String sql = "SELECT * FROM inquiries";
        List<Inquiry> inquiries = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            while (resultSet.next()) {
                Inquiry inquiry = new Inquiry(
                        resultSet.getInt("inquiry_id"),
                        resultSet.getInt("user_id"),
                        resultSet.getString("product_name"),
                        resultSet.getInt("quantity"),
                        resultSet.getDate("inquiry_date"),
                        resultSet.getString("status")
                );
                inquiries.add(inquiry);
            }
        }
        return inquiries;
    }
}
