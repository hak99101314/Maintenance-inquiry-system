package com.ruiyang.controller;

import com.ruiyang.dao.UserDao;
import com.ruiyang.model.User;
import com.ruiyang.util.DBUtil;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

import java.io.IOException;
import java.security.NoSuchAlgorithmException;
import java.sql.Connection;
import java.sql.SQLException;

@WebServlet("/login")
public class LoginServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String username = request.getParameter("username");
        String password = request.getParameter("password");

        try (Connection conn = DBUtil.getConnection()) {
            UserDao userDao = new UserDao(conn);
            User user = userDao.getUserByUsername(username);

            if (user != null && user.getPassword().equals(UserDao.hashPassword(password))) {
                // 設置 Session
                HttpSession session = request.getSession();
                session.setAttribute("user", user);

                // 根據角色重定向到不同的頁面
                switch (user.getRole()) {
                    case "customer":
                        response.sendRedirect("customer_home.jsp");
                        break;
                    case "staff":
                        response.sendRedirect("staff_dashboard.jsp");
                        break;
                    case "admin":
                        response.sendRedirect("admin_dashboard.jsp");
                        break;
                    default:
                        request.setAttribute("errorMessage", "角色不明，請聯繫系統管理員");
                        request.getRequestDispatcher("login.html").forward(request, response);
                }
            } else {
                request.setAttribute("errorMessage", "帳號或密碼錯誤");
                request.getRequestDispatcher("login.html").forward(request, response);
            }
        } catch (SQLException | NoSuchAlgorithmException e) {
            e.printStackTrace();
            request.setAttribute("errorMessage", "系統錯誤，請稍後再試");
            request.getRequestDispatcher("login.html").forward(request, response);
        }
    }
}
