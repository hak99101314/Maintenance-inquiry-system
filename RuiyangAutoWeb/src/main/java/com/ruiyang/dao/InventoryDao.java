package com.ruiyang.dao;

import com.ruiyang.model.Inventory;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class InventoryDao {

    // 更新庫存（增加或減少）
    public boolean updateInventory(String partName, int quantity) throws SQLException {
        String sql = "UPDATE inventory SET quantity = quantity + ? WHERE part_name = ?";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, quantity);
            statement.setString(2, partName);

            return statement.executeUpdate() > 0;
        }
    }

    // 查詢所有庫存
    public List<Inventory> getAllInventory() throws SQLException {
        String sql = "SELECT * FROM inventory";
        List<Inventory> inventoryList = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql);
             ResultSet resultSet = statement.executeQuery()) {
            while (resultSet.next()) {
                Inventory inventory = new Inventory(
                        resultSet.getInt("part_id"),
                        resultSet.getString("part_name"),
                        resultSet.getInt("quantity")
                );
                inventoryList.add(inventory);
            }
        }
        return inventoryList;
    }
}
