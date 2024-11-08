package com.ruiyang.dao;

import com.ruiyang.model.PurchaseRecord;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class PurchaseRecordDao {

    // 新增進貨記錄到資料庫
    public boolean createPurchaseRecord(PurchaseRecord record) throws SQLException {
        String sql = "INSERT INTO purchase_records (part_name, quantity, supplier, purchase_date, price) VALUES (?, ?, ?, NOW(), ?)";
        
        System.out.println("準備新增進貨記錄...");

        try (Connection connection = DBUtil.getConnection(); 
             PreparedStatement statement = connection.prepareStatement(sql)) {
            
            System.out.println("資料庫連接成功，開始設定參數...");
            
            // 設定參數
            statement.setString(1, record.getPartName());
            statement.setInt(2, record.getQuantity());
            statement.setString(3, record.getSupplier());
            statement.setDouble(4, record.getPrice());

            System.out.println("參數設定完成，準備執行插入操作...");
            
            // 執行插入並檢查是否成功
            boolean isSuccess = statement.executeUpdate() > 0;
            
            if (isSuccess) {
                System.out.println("新增進貨記錄成功！");
            } else {
                System.out.println("新增進貨記錄失敗！");
            }
            return isSuccess;
        } catch (SQLException e) {
            System.out.println("新增進貨記錄過程中發生錯誤：" + e.getMessage());
            throw e;
        }
    }

    // 查詢資料庫中的所有進貨記錄
    public List<PurchaseRecord> getAllPurchaseRecords() throws SQLException {
        String sql = "SELECT * FROM purchase_records";
        List<PurchaseRecord> records = new ArrayList<>();
        
        System.out.println("準備查詢所有進貨記錄...");

        try (Connection connection = DBUtil.getConnection(); 
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {

            System.out.println("資料庫連接成功，開始執行查詢...");

            // 遍歷結果集並轉換為 PurchaseRecord 物件
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
            System.out.println("查詢完成，共查得 " + records.size() + " 筆進貨記錄。");
        } catch (SQLException e) {
            System.out.println("查詢進貨記錄過程中發生錯誤：" + e.getMessage());
            throw e;
        }
        return records;
    }
}
