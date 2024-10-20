package com.ruiyang.dao;

import com.ruiyang.model.RepairRecord;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class RepairRecordDao {

    // 新增維修記錄
    public boolean createRepairRecord(RepairRecord record) throws SQLException {
        String sql = "INSERT INTO repair_records (order_id, repair_date, repair_status, technician_id) VALUES (?, NOW(), ?, ?)";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, record.getOrderId());
            statement.setString(2, record.getRepairStatus());
            statement.setInt(3, record.getTechnicianId());

            return statement.executeUpdate() > 0;
        }
    }

    // 更新維修狀態
    public boolean updateRepairStatus(int recordId, String status) throws SQLException {
        String sql = "UPDATE repair_records SET repair_status = ? WHERE record_id = ?";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, status);
            statement.setInt(2, recordId);

            return statement.executeUpdate() > 0;
        }
    }

    // 查詢某訂單的維修記錄
    public List<RepairRecord> getRepairRecordsByOrderId(int orderId) throws SQLException {
        String sql = "SELECT * FROM repair_records WHERE order_id = ?";
        List<RepairRecord> records = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, orderId);
            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    RepairRecord record = new RepairRecord(
                            resultSet.getInt("record_id"),
                            resultSet.getInt("order_id"),
                            resultSet.getDate("repair_date"),
                            resultSet.getString("repair_status"),
                            resultSet.getInt("technician_id")
                    );
                    records.add(record);
                }
            }
        }
        return records;
    }
}
