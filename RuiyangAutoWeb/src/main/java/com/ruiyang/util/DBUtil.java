package com.ruiyang.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class DBUtil {
    // 替換為你的數據庫 URL、用戶名和密碼
    private static final String URL = "jdbc:mysql://localhost:3306/睿煬企業社?useUnicode=true&characterEncoding=utf8"; 
    private static final String USER = "root"; // 數據庫用戶名
    private static final String PASSWORD = "karry,roy,jackson"; // 數據庫密碼

    // 獲取數據庫連接
    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(URL, USER, PASSWORD);
    }

    // 關閉資源（可選）
    public static void close(Connection connection) {
        if (connection != null) {
            try {
                connection.close();
            } catch (SQLException e) {
                e.printStackTrace();
            }
        }
    }
}
