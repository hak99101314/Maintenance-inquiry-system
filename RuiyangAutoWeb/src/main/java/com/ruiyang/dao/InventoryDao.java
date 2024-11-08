package com.ruiyang.dao;

import com.ruiyang.model.Inventory;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class InventoryDao {

    public InventoryDao(Connection conn) {
		// TODO Auto-generated constructor stub
	}

	// 更新庫存方法（用於增加或減少指定零件的庫存數量）
    public boolean updateInventory(String partName, int quantity) throws SQLException {
        System.out.println("開始更新庫存...");
        
        // SQL語句，將指定零件的數量更新為當前數量加上提供的數量
        String sql = "UPDATE inventory SET quantity = quantity + ? WHERE part_name = ?";
        
        // 使用自動資源管理來管理資料庫連接和PreparedStatement物件
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            
            System.out.println("資料庫連接成功。");
            
            // 設置參數值，第一個參數為增加或減少的數量，第二個參數為零件名稱
            statement.setInt(1, quantity);
            statement.setString(2, partName);
            System.out.println("SQL參數設置完成：quantity=" + quantity + ", partName=" + partName);

            // 執行更新操作，並返回結果（大於0表示成功更新）
            boolean isUpdated = statement.executeUpdate() > 0;
            if (isUpdated) {
                System.out.println("庫存更新成功！");
            } else {
                System.out.println("庫存更新失敗，找不到指定零件。");
            }
            return isUpdated;

        } catch (SQLException e) {
            System.out.println("更新庫存過程中發生錯誤：" + e.getMessage());
            throw e;
        }
    }

    // 查詢所有庫存方法，返回一個包含所有庫存資料的列表
    public List<Inventory> getAllInventory() throws SQLException {
        System.out.println("開始查詢所有庫存...");
        
        // SQL查詢語句，選取所有庫存記錄
        String sql = "SELECT * FROM inventory";
        
        // 用於儲存查詢結果的庫存列表
        List<Inventory> inventoryList = new ArrayList<>();
        
        // 使用自動資源管理來管理資料庫連接、PreparedStatement和ResultSet物件
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            
            System.out.println("資料庫連接成功，開始執行查詢...");
            
            // 迭代ResultSet中的每一行資料，創建Inventory對象並添加到列表中
            while (resultSet.next()) {
                Inventory inventory = new Inventory(
                        resultSet.getInt("part_id"),       // 取得零件ID
                        resultSet.getString("part_name"),  // 取得零件名稱
                        resultSet.getInt("quantity")       // 取得庫存數量
                );
                System.out.println("取得庫存資料：" + inventory);
                inventoryList.add(inventory);
            }
            System.out.println("所有庫存查詢完畢，總計" + inventoryList.size() + "筆記錄。");

        } catch (SQLException e) {
            System.out.println("查詢庫存過程中發生錯誤：" + e.getMessage());
            throw e;
        }
        
        // 返回包含所有庫存資料的列表
        return inventoryList;
    }
}
