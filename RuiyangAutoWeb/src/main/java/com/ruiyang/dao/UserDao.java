package com.ruiyang.dao;

import com.ruiyang.model.User;
import com.ruiyang.util.DBUtil;

import java.sql.*;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

public class UserDao {

    private static final String SQL_INSERT_USER = 
            "INSERT INTO users (username, password_hash, role, full_name, contact_number, address, email, created_date, is_enabled) " +
            "VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
    
    private static final String SQL_SELECT_USER_BY_USERNAME = 
            "SELECT * FROM users WHERE username = ?";

    // 新增用戶（註冊用戶）
    public boolean registerUser(User user) throws SQLException, NoSuchAlgorithmException {
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(SQL_INSERT_USER)) {
            statement.setString(1, user.getUsername());
            statement.setString(2, hashPassword(user.getPassword())); // 密碼加密
            statement.setString(3, user.getRole()); // 預設角色（如 "customer"）
            statement.setString(4, user.getFullName());
            statement.setString(5, user.getContactNumber());
            statement.setString(6, user.getAddress());
            statement.setString(7, user.getEmail());

            return statement.executeUpdate() > 0;
        }
    }

    // 根據用戶名查詢用戶信息
    public User getUserByUsername(String username) throws SQLException {
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(SQL_SELECT_USER_BY_USERNAME)) {
            statement.setString(1, username);
            try (ResultSet resultSet = statement.executeQuery()) {
                if (resultSet.next()) {
                    return new User(
                        resultSet.getInt("user_id"),
                        resultSet.getString("username"),
                        resultSet.getString("password_hash"),
                        resultSet.getString("role"),
                        resultSet.getString("full_name"),
                        resultSet.getString("contact_number"),
                        resultSet.getString("address"),
                        resultSet.getString("email"),
                        resultSet.getDate("created_date"),
                        resultSet.getBoolean("is_enabled")
                    );
                }
            }
        }
        return null;
    }

    // 密碼加密方法
    public static String hashPassword(String password) throws NoSuchAlgorithmException {
        MessageDigest md = MessageDigest.getInstance("SHA-256");
        md.update(password.getBytes());
        byte[] digest = md.digest();
        StringBuilder sb = new StringBuilder();
        for (byte b : digest) {
            sb.append(String.format("%02x", b));
        }
        return sb.toString();
    }
}
