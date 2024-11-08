package com.ruiyang.controller;

import java.io.IOException;
import java.security.NoSuchAlgorithmException;
import java.sql.Connection;
import java.sql.SQLException;

import com.ruiyang.dao.UserDao;
import com.ruiyang.model.User;
import com.ruiyang.util.DBUtil;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

@WebServlet("/login")
public class LoginServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        // 取得使用者輸入的帳號和密碼
        String username = request.getParameter("username");
        String password = request.getParameter("password");

        System.out.println("Received login request with username: " + username);

        try (Connection conn = DBUtil.getConnection()) {
            System.out.println("Database connection established");

            UserDao userDao = new UserDao(conn);
            User user = userDao.getUserByUsername(username);

            if (user != null) {
                System.out.println("User found in database: " + user.getUsername());

                // 檢查密碼是否正確
                if (user.getPassword().equals(UserDao.hashPassword(password))) {
                    System.out.println("Password verification successful");

                    // 設置 Session
                    HttpSession session = request.getSession();
                    session.setAttribute("user", user);
                    System.out.println("Session created for user: " + user.getUsername());

                    // 根據角色重定向到不同的頁面
                    switch (user.getRole()) {
                        case "customer":
                            System.out.println("Redirecting to customer home page");
                            response.sendRedirect("customer_home.jsp");
                            break;
                        case "staff":
                            System.out.println("Redirecting to staff dashboard");
                            response.sendRedirect("staff_dashboard.jsp");
                            break;
                        case "admin":
                            System.out.println("Redirecting to admin dashboard");
                            response.sendRedirect("admin_dashboard.jsp");
                            break;
                        default:
                            System.out.println("Unknown role, redirecting to login page with error message");
                            request.setAttribute("errorMessage", "角色不明，請聯繫系統管理員");
                            request.getRequestDispatcher("login.html").forward(request, response);
                    }
                } else {
                    System.out.println("Password verification failed");
                    request.setAttribute("errorMessage", "帳號或密碼錯誤");
                    request.getRequestDispatcher("login.html").forward(request, response);
                }
            } else {
                System.out.println("User not found in database");
                request.setAttribute("errorMessage", "帳號或密碼錯誤");
                request.getRequestDispatcher("login.html").forward(request, response);
            }
        } catch (SQLException | NoSuchAlgorithmException e) {
            System.out.println("Exception occurred: " + e.getMessage());
            e.printStackTrace();
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("login.html").forward(request, response);
        }
    }
}
