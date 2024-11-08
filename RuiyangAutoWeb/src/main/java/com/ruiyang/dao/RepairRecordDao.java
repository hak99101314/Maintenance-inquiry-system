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
             
            // 設定參數
            statement.setInt(1, record.getOrderId());
            statement.setString(2, record.getRepairStatus());
            statement.setInt(3, record.getTechnicianId());
            System.out.println("已設置插入記錄的參數。");

            // 執行插入
            boolean isInserted = statement.executeUpdate() > 0;
            System.out.println("插入記錄：" + (isInserted ? "成功" : "失敗"));
            return isInserted;
        } catch (SQLException e) {
            System.err.println("新增維修記錄時發生錯誤：" + e.getMessage());
            throw e;
        }
    }

    // 更新維修狀態
    public boolean updateRepairStatus(int recordId, String status) throws SQLException {
        String sql = "UPDATE repair_records SET repair_status = ? WHERE record_id = ?";
        
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
             
            // 設定參數
            statement.setString(1, status);
            statement.setInt(2, recordId);
            System.out.println("已設置更新狀態的參數。");

            // 執行更新
            boolean isUpdated = statement.executeUpdate() > 0;
            System.out.println("更新狀態：" + (isUpdated ? "成功" : "失敗"));
            return isUpdated;
        } catch (SQLException e) {
            System.err.println("更新維修狀態時發生錯誤：" + e.getMessage());
            throw e;
        }
    }

    // 查詢某訂單的所有維修記錄
    public List<RepairRecord> getRepairRecordsByOrderId(int orderId) throws SQLException {
        String sql = "SELECT * FROM repair_records WHERE order_id = ?";
        List<RepairRecord> records = new ArrayList<>();
        
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
             
            // 設定參數
            statement.setInt(1, orderId);
            System.out.println("已設置查詢參數。");

            // 執行查詢
            try (ResultSet resultSet = statement.executeQuery()) {
                System.out.println("查詢成功，正在處理結果集。");

                // 處理查詢結果
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
                System.out.println("查詢結果處理完成，共找到 " + records.size() + " 筆記錄。");
            }
        } catch (SQLException e) {
            System.err.println("查詢維修記錄時發生錯誤：" + e.getMessage());
            throw e;
        }
        
        return records;
    }

	public List<RepairRecord> getRepairRecords(String plateNumber, String phoneNumber) {
		// TODO Auto-generated method stub
		return null;
	}
}
