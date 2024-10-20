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
            statement.setInt(1, order.getUserId());
            statement.setInt(2, order.getQuotationId());
            statement.setString(3, "待維修");

            return statement.executeUpdate() > 0;
        }
    }

    // 更新訂單狀態
    public boolean updateOrderStatus(int orderId, String status) throws SQLException {
        String sql = "UPDATE orders SET status = ? WHERE order_id = ?";
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setString(1, status);
            statement.setInt(2, orderId);

            return statement.executeUpdate() > 0;
        }
    }

    // 查詢會員的所有訂單
    public List<Order> getOrdersByUserId(int userId) throws SQLException {
        String sql = "SELECT * FROM orders WHERE user_id = ?";
        List<Order> orders = new ArrayList<>();
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {
            statement.setInt(1, userId);
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
                }
            }
        }
        return orders;
    }
}
