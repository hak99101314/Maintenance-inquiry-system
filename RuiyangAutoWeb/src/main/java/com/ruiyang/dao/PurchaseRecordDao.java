package com.ruiyang.dao;

import com.ruiyang.model.PurchaseRecord;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class PurchaseRecordDao {

    // 新增進貨記錄
    public boolean createPurchaseRecord(PurchaseRecord record) throws SQLException {
        String sql = "INSERT INTO purchase_records (part_name, quantity, supplier, purchase_date, price) VALUES (?, ?, ?, NOW(), ?)";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, record.getPartName());
            statement.setInt(2, record.getQuantity());
            statement.setString(3, record.getSupplier());
            statement.setDouble(4, record.getPrice());

            return statement.executeUpdate() > 0;
        }
    }

    // 查詢所有進貨記錄
    public List<PurchaseRecord> getAllPurchaseRecords() throws SQLException {
        String sql = "SELECT * FROM purchase_records";
        List<PurchaseRecord> records = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            while (resultSet.next()) {
                PurchaseRecord record = new PurchaseRecord(
                        resultSet.getInt("purchase_id"),
                        resultSet.getString("part_name"),
                        resultSet.getInt("quantity"),
                        resultSet.getString("supplier"),
                        resultSet.getDate("purchase_date"),
                        resultSet.getDouble("price")
                );
                records.add(record);
            }
        }
        return records;
    }
}
