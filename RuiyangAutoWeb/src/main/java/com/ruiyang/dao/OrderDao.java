package com.ruiyang.dao;

import com.ruiyang.model.Order;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class OrderDao {


	// 新增訂單
    public boolean createOrder(Order order) throws SQLException {
        String sql = "INSERT INTO orders (user_id, quotation_id, order_date, status) VALUES (?, ?, NOW(), ?)";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            System.out.println("資料庫連接成功，準備新增訂單");

            // 設定SQL語句的參數值
            statement.setInt(1, order.getUserId());
            statement.setInt(2, order.getQuotationId());
            statement.setString(3, "待維修");

            // 執行更新，並檢查是否成功新增訂單
            boolean result = statement.executeUpdate() > 0;
            System.out.println("訂單新增 " + (result ? "成功" : "失敗"));
            return result;
        } catch (SQLException e) {
            System.err.println("新增訂單時發生錯誤: " + e.getMessage());
            throw e;
        }
    }

    // 更新訂單狀態
    public boolean updateOrderStatus(int orderId, String status) throws SQLException {
        String sql = "UPDATE orders SET status = ? WHERE order_id = ?";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            System.out.println("資料庫連接成功，準備更新訂單狀態");

            // 設定SQL語句的參數值
            statement.setString(1, status);
            statement.setInt(2, orderId);

            // 執行更新，並檢查是否成功更新狀態
            boolean result = statement.executeUpdate() > 0;
            System.out.println("訂單狀態更新 " + (result ? "成功" : "失敗"));
            return result;
        } catch (SQLException e) {
            System.err.println("更新訂單狀態時發生錯誤: " + e.getMessage());
            throw e;
        }
    }

    // 查詢會員的所有訂單
    public List<Order> getOrdersByUserId(int userId) throws SQLException {
        String sql = "SELECT * FROM orders WHERE user_id = ?";
        List<Order> orders = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            System.out.println("資料庫連接成功，準備查詢會員的所有訂單");

            statement.setInt(1, userId);

            // 執行查詢並處理查詢結果
            try (ResultSet resultSet = statement.executeQuery()) {
                while (resultSet.next()) {
                    Order order = new Order(
                            resultSet.getInt("order_id"),
                            resultSet.getInt("user_id"),
                            resultSet.getInt("quotation_id"),
                            resultSet.getDate("order_date"),
                            resultSet.getString("status")
                    );
                    orders.add(order);
                    System.out.println("取得訂單: ID=" + order.getOrderId());
                }
            }
        } catch (SQLException e) {
            System.err.println("查詢訂單時發生錯誤: " + e.getMessage());
            throw e;
        }
        System.out.println("查詢完成，共找到 " + orders.size() + " 筆訂單");
        return orders;
    }
}
