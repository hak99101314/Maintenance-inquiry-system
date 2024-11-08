package com.ruiyang.filter;

import java.io.IOException;
import java.nio.file.DirectoryStream.Filter;

import jakarta.servlet.FilterChain;
import jakarta.servlet.FilterConfig;
import jakarta.servlet.ServletException;
import jakarta.servlet.ServletRequest;
import jakarta.servlet.ServletResponse;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

@SuppressWarnings("rawtypes")
@jakarta.servlet.annotation.WebFilter("/*") // 檢查所有請求，或指定特定的路徑
public abstract class AuthenticationFilter implements Filter {

    public void doFilter(ServletRequest request, ServletResponse response, FilterChain chain)
            throws IOException, ServletException {
        
        HttpServletRequest httpRequest = (HttpServletRequest) request;
        HttpServletResponse httpResponse = (HttpServletResponse) response;

        String requestURI = httpRequest.getRequestURI();
        System.out.println("Starting filter process.");
        System.out.println("Requested URI: " + requestURI);

        HttpSession session = httpRequest.getSession(false);
        boolean loggedIn = (session != null && session.getAttribute("user") != null);

        System.out.println("Session retrieved: " + (session != null));
        System.out.println("User found in session: " + loggedIn);

        // 如果用戶未登錄，且正在訪問受限頁面
        if (!loggedIn && isRestrictedPage(requestURI)) {
            System.out.println("User not logged in and accessing restricted page. Redirecting to login.");
            httpResponse.sendRedirect(httpRequest.getContextPath() + "/login.jsp");
        } else {
            chain.doFilter(request, response);
        }
    }

    private boolean isRestrictedPage(String uri) {
        // 判斷當前訪問的頁面是否為受限頁面
        return !uri.endsWith("login.jsp") && !uri.endsWith("register.jsp") && !uri.contains("/public/");
    }

    public void init(FilterConfig filterConfig) throws ServletException {
        // 初始化代碼（如果有需要）
    }

    public void destroy() {
        // 清理資源代碼（如果有需要）
    }
}
