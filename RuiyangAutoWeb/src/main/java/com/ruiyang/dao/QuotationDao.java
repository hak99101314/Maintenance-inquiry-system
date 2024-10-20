package com.ruiyang.dao;

import com.ruiyang.model.Quotation;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class QuotationDao {

    // 新增報價
    public boolean createQuotation(Quotation quotation) throws SQLException {
        String sql = "INSERT INTO quotations (inquiry_id, quoted_amount, employee_id, quotation_date) VALUES (?, ?, ?, NOW())";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, quotation.getInquiryId());
            statement.setDouble(2, quotation.getQuotedAmount());
            statement.setInt(3, quotation.getEmployeeId());

            return statement.executeUpdate() > 0;
        }
    }

    // 查詢某個詢價的報價
    public Quotation getQuotationByInquiryId(int inquiryId) throws SQLException {
        String sql = "SELECT * FROM quotations WHERE inquiry_id = ?";
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
                }
            }
        }
        return null;
    }
}
