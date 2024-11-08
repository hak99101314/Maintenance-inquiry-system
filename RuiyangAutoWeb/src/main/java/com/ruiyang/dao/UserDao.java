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

    public UserDao(Connection conn) {
		// TODO Auto-generated constructor stub
	}

	// 新增用戶到資料庫 (用於註冊新用戶)
    public boolean registerUser(User user) throws SQLException, NoSuchAlgorithmException {
        System.out.println("開始註冊用戶");
        
        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(SQL_INSERT_USER)) {

            System.out.println("資料庫連線成功");
            
            statement.setString(1, user.getUsername());
            System.out.println("設定用戶名稱成功");

            statement.setString(2, hashPassword(user.getPassword())); // 使用 hashPassword 方法加密密碼
            System.out.println("密碼加密成功");

            statement.setString(3, user.getRole()); // 設定角色，例如 "customer" 預設角色
            System.out.println("設定角色成功");

            statement.setString(4, user.getFullName());
            System.out.println("設定全名成功");

            statement.setString(5, user.getContactNumber());
            System.out.println("設定聯絡電話成功");

            statement.setString(6, user.getAddress());
            System.out.println("設定地址成功");

            statement.setString(7, user.getEmail());
            System.out.println("設定電子郵件成功");

            int rowsInserted = statement.executeUpdate();
            System.out.println("執行 SQL 插入語句，影響行數: " + rowsInserted);

            return rowsInserted > 0;
        } catch (SQLException e) {
            System.err.println("註冊用戶時出錯: " + e.getMessage());
            throw e;
        }
    }

    private String hashPassword(Object password) {
		// TODO Auto-generated method stub
		return null;
	}

	// 根據用戶名查詢用戶信息
    public User getUserByUsername(String username) throws SQLException {
        System.out.println("開始查詢用戶：" + username);

        try (Connection connection = DBUtil.getConnection();
             PreparedStatement statement = connection.prepareStatement(SQL_SELECT_USER_BY_USERNAME)) {
             
            System.out.println("資料庫連線成功");

            statement.setString(1, username);
            System.out.println("設定用戶名查詢參數成功");

            try (ResultSet resultSet = statement.executeQuery()) {
                System.out.println("執行查詢語句");

                if (resultSet.next()) {
                    System.out.println("用戶存在，開始封裝用戶資料");

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
                } else {
                    System.out.println("用戶不存在");
                }
            }
        } catch (SQLException e) {
            System.err.println("查詢用戶時出錯: " + e.getMessage());
            throw e;
        }
        return null;
    }

    // 密碼加密方法 (使用 SHA-256 演算法)
    public static String hashPassword(String password) throws NoSuchAlgorithmException {
        System.out.println("開始加密密碼");

        MessageDigest md = MessageDigest.getInstance("SHA-256");
        md.update(password.getBytes());
        byte[] digest = md.digest();

        System.out.println("密碼加密完成");

        StringBuilder sb = new StringBuilder();
        for (byte b : digest) {
            sb.append(String.format("%02x", b));
        }
        System.out.println("密碼加密字串生成成功");
        return sb.toString();
    }
}
