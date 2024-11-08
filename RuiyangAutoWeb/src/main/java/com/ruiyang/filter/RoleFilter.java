package com.ruiyang.filter;

import java.io.IOException;

import com.ruiyang.model.User;

import jakarta.servlet.Filter;
import jakarta.servlet.FilterChain;
import jakarta.servlet.FilterConfig;
import jakarta.servlet.ServletException;
import jakarta.servlet.ServletRequest;
import jakarta.servlet.ServletResponse;
import jakarta.servlet.annotation.WebFilter;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

@WebFilter("/*") // 設置過濾器，攔截所有請求
public class RoleFilter implements Filter {

    @Override
    public void init(FilterConfig fConfig) throws ServletException {
        System.out.println("Filter initialized."); // 確認過濾器初始化
    }

    @Override
    public void doFilter(ServletRequest request, ServletResponse response, FilterChain chain)
            throws IOException, ServletException {

        System.out.println("Starting filter process."); // 確認過濾器啟動
        HttpServletRequest req = (HttpServletRequest) request;
        HttpServletResponse res = (HttpServletResponse) response;
        
        HttpSession session = req.getSession(false);
        System.out.println("Session retrieved: " + (session != null)); // 顯示是否成功獲取會話

        String uri = req.getRequestURI();
        System.out.println("Requested URI: " + uri); // 列印請求的資源路徑
        
        User user = (session != null) ? (User) session.getAttribute("user") : null;
        System.out.println("User found in session: " + (user != null)); // 顯示用戶是否存在於會話中

        // 檢查用戶是否已登錄
        if (user == null && !uri.endsWith("login.html") && !uri.endsWith("register.html")) {
            System.out.println("User not logged in and accessing restricted page. Redirecting to login."); // 若用戶未登入，顯示訊息
            res.sendRedirect("login.html");
            return; 
        }

        if (user != null) {
            String role = user.getRole();
            System.out.println("User role: " + role); // 列印用戶角色

            if (uri.contains("/staff_dashboard") && !"staff".equals(role) && !"admin".equals(role)) {
                System.out.println("Access denied to staff dashboard. Redirecting to unauthorized page."); // 訪問員工頁面無權限時
                res.sendRedirect("unauthorized.html");
                return;
            }

            if (uri.contains("/admin_dashboard") && !"admin".equals(role)) {
                System.out.println("Access denied to admin dashboard. Redirecting to unauthorized page."); // 訪問管理員頁面無權限時
                res.sendRedirect("unauthorized.html");
                return;
            }
        }

        System.out.println("Access granted. Passing request to the next filter or resource."); // 若檢查通過
        chain.doFilter(request, response);
    }

    @Override
    public void destroy() {
        System.out.println("Filter destroyed."); // 確認過濾器銷毀
    }
}
