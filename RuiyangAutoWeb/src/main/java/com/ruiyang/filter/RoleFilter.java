package com.ruiyang.filter;

import com.ruiyang.model.User;

import javax.servlet.Filter;
import javax.servlet.FilterChain;
import javax.servlet.FilterConfig;
import javax.servlet.ServletException;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;
import javax.servlet.annotation.WebFilter;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;

@WebFilter("/*")
public class RoleFilter implements Filter {

    @Override
    public void init(FilterConfig fConfig) throws ServletException {}

    @Override
    public void doFilter(ServletRequest request, ServletResponse response, FilterChain chain)
            throws IOException, ServletException {
        
        HttpServletRequest req = (HttpServletRequest) request;
        HttpServletResponse res = (HttpServletResponse) response;
        HttpSession session = req.getSession(false);

        String uri = req.getRequestURI();
        User user = (session != null) ? (User) session.getAttribute("user") : null;

        // 如果用戶尚未登錄，且請求的資源不是登錄或註冊頁面
        if (user == null && !uri.endsWith("login.html") && !uri.endsWith("register.html")) {
            res.sendRedirect("login.html");
            return; // 確保重定向後不會繼續進行過濾
        }

        // 用戶已登錄，檢查角色訪問權限
        if (user != null) {
            String role = user.getRole();

            // 檢查對員工頁面的訪問權限
            if (uri.contains("/staff_dashboard") && !"staff".equals(role) && !"admin".equals(role)) {
                res.sendRedirect("unauthorized.html");
                return;
            }

            // 檢查對管理員頁面的訪問權限
            if (uri.contains("/admin_dashboard") && !"admin".equals(role)) {
                res.sendRedirect("unauthorized.html");
                return;
            }
        }

        // 通過過濾鏈
        chain.doFilter(request, response);
    }

    @Override
    public void destroy() {}
}
