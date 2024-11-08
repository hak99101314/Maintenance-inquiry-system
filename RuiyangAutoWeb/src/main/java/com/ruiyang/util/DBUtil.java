package com.ruiyang.util;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class DBUtil {

    // 資料庫的 URL、用戶名和密碼
    private static final String URL = "jdbc:mysql://localhost:3306/睿煬企業社?useUnicode=true&characterEncoding=utf8";
    private static final String USER = "root"; // 用戶名
    private static final String PASSWORD = "karry,roy,jackson"; // 密碼

    // 獲取資料庫連接
    public static Connection getConnection() throws SQLException {
        System.out.println("開始嘗試連接到資料庫...");
        Connection connection = DriverManager.getConnection(URL, USER, PASSWORD);
        if (connection != null) {
            System.out.println("資料庫連接成功！");
        } else {
            System.out.println("資料庫連接失敗！");
        }
        return connection;
    }

    // 關閉資料庫連接
    public static void close(Connection connection) {
        if (connection != null) { // 確保連接不為空
            try {
                System.out.println("開始關閉資料庫連接...");
                connection.close();
                System.out.println("資料庫連接已成功關閉！");
            } catch (SQLException e) {
                System.out.println("關閉資料庫連接時發生錯誤！");
                e.printStackTrace(); // 列印異常堆疊追蹤
            }
        } else {
            System.out.println("無需關閉，因為連接為空！");
        }
    }
}
