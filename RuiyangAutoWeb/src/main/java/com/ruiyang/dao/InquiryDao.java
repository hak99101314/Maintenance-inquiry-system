package com.ruiyang.dao;

import com.ruiyang.model.Inquiry;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class InquiryDao {

    public InquiryDao(Connection conn) {
		// TODO Auto-generated constructor stub
	}

	// 新增詢價單
    public boolean createInquiry(Inquiry inquiry) throws SQLException {
        String sql = "INSERT INTO inquiries (user_id, product_name, quantity, inquiry_date, status) VALUES (?, ?, ?, NOW(), 'submitted')";
        System.out.println("開始新增詢價單...");

        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {

            System.out.println("資料庫連接成功");

            // 設置SQL語句中的參數
            statement.setInt(1, inquiry.getUserId());
            System.out.println("設定 user_id：" + inquiry.getUserId());

            statement.setString(2, inquiry.getProductName());
            System.out.println("設定 product_name：" + inquiry.getProductName());

            statement.setInt(3, inquiry.getQuantity());
            System.out.println("設定 quantity：" + inquiry.getQuantity());

            // 執行插入操作，返回影響的行數
            boolean isInserted = statement.executeUpdate() > 0;
            System.out.println(isInserted ? "新增成功" : "新增失敗");

            return isInserted;
        } catch (SQLException e) {
            System.err.println("新增詢價單時出現錯誤：" + e.getMessage());
            throw e;
        }
    }

    // 查詢會員的所有詢價單
    public List<Inquiry> getInquiriesByUserId(int userId) throws SQLException {
        String sql = "SELECT * FROM inquiries WHERE user_id = ?";
        List<Inquiry> inquiries = new ArrayList<>();

        System.out.println("開始查詢會員詢價單，user_id：" + userId);

        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {

            System.out.println("資料庫連接成功");

            // 設定user_id參數
            statement.setInt(1, userId);
            System.out.println("設定查詢 user_id：" + userId);

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
                    System.out.println("查詢到詢價單：" + inquiry);
                }
            }
            System.out.println("查詢完成，共找到 " + inquiries.size() + " 筆詢價單");
        } catch (SQLException e) {
            System.err.println("查詢詢價單時出現錯誤：" + e.getMessage());
            throw e;
        }
        return inquiries;
    }

    // 查詢所有詢價單（供員工查看）
    public List<Inquiry> getAllInquiries() throws SQLException {
        String sql = "SELECT * FROM inquiries";
        List<Inquiry> inquiries = new ArrayList<>();

        System.out.println("開始查詢所有詢價單...");

        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {

            System.out.println("資料庫連接成功");

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
                System.out.println("查詢到詢價單：" + inquiry);
            }
            System.out.println("查詢完成，共找到 " + inquiries.size() + " 筆詢價單");
        } catch (SQLException e) {
            System.err.println("查詢所有詢價單時出現錯誤：" + e.getMessage());
            throw e;
        }
        return inquiries;
    }
}
