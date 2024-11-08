package com.ruiyang.dao;

import com.ruiyang.model.Quotation;
import com.ruiyang.util.DBUtil;

import java.sql.*;

public class QuotationDao {

    /**
     * 新增一筆報價資料到資料庫
     * @param quotation 報價對象，包含報價相關的資訊
     * @return 新增成功返回 true，失敗返回 false
     * @throws SQLException 當資料庫操作失敗時，拋出 SQLException
     */
    public boolean createQuotation(Quotation quotation) throws SQLException {
        String sql = "INSERT INTO quotations (inquiry_id, quoted_amount, employee_id, quotation_date) VALUES (?, ?, ?, NOW())";
        System.out.println("開始新增報價資料...");

        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {

            statement.setInt(1, quotation.getInquiryId());
            statement.setDouble(2, quotation.getQuotedAmount());
            statement.setInt(3, quotation.getEmployeeId());

            boolean isInserted = statement.executeUpdate() > 0;
            System.out.println(isInserted ? "報價資料新增成功！" : "報價資料新增失敗！");
            return isInserted;

        } catch (SQLException e) {
            System.err.println("新增報價時出現錯誤：" + e.getMessage());
            throw e;
        }
    }

    /**
     * 根據詢價 ID 查詢報價
     * @param inquiryId 詢價的唯一識別 ID
     * @return 對應的 Quotation 物件，若找不到則返回 null
     * @throws SQLException 當資料庫操作失敗時，拋出 SQLException
     */
    public Quotation getQuotationByInquiryId(int inquiryId) throws SQLException {
        String sql = "SELECT * FROM quotations WHERE inquiry_id = ?";
        System.out.println("查詢報價資料，詢價 ID: " + inquiryId);

        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {

            statement.setInt(1, inquiryId);

            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return new Quotation(
                            resultSet.getInt("quotation_id"),
                            resultSet.getInt("inquiry_id"),
                            resultSet.getDouble("quoted_amount"),
                            resultSet.getInt("employee_id"),
                            resultSet.getDate("quotation_date")
                    );
                } else {
                    System.out.println("未找到符合的報價資料");
                }
            }
        } catch (SQLException e) {
            System.err.println("查詢報價資料時出現錯誤：" + e.getMessage());
            throw e;
        }
        return null;
    }
}
