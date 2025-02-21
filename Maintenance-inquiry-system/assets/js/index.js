// 動態生成導航列
document.addEventListener('DOMContentLoaded', function () {
    const navItems = document.getElementById('nav-items');
    const welcomeMessage = document.getElementById('welcome-message');
    const userRole = localStorage.getItem('user_role');
    const userName = localStorage.getItem('username') || '未登入';

    // 清空導航列
    navItems.innerHTML = '';

    // 根據角色生成導航列
    if (userRole === 'admin' || userRole === 'staff') {
        // 後端人員專用功能
        navItems.innerHTML += `
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.html">後台管理</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_users.html">用戶管理</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_inventory.html">庫存管理</a></li>
            <li class="nav-item"><a class="nav-link" href="admin_orders.html">訂單管理</a></li>
        `;
    } else if (userRole === 'customer') {
        // 會員專用功能
        navItems.innerHTML += `
            <li class="nav-item"><a class="nav-link" href="profile.html">個人資料</a></li>
            <li class="nav-item"><a class="nav-link" href="query.html">查詢維修紀錄</a></li>
            <li class="nav-item"><a class="nav-link" href="appointments.html">預約維修</a></li>
            <li class="nav-item"><a class="nav-link" href="survey.html">滿意度調查</a></li>
        `;
    } else {
        // 未登入顯示登入與註冊選項
        navItems.innerHTML += `
            <li class="nav-item"><a class="nav-link" href="login.html">登入</a></li>
            <li class="nav-item"><a class="nav-link" href="register.html">註冊</a></li>
        `;
    }

    // 添加登出按鈕
    if (userRole) {
        navItems.innerHTML += `
            <li class="nav-item"><a class="nav-link" href="#" onclick="logout()">登出</a></li>
        `;
    }

    // 更新歡迎訊息
    if (welcomeMessage) {
        welcomeMessage.innerText = `歡迎 ${userName}`;
    }
});

// 登出功能
function logout() {
    // 清除用戶數據
    localStorage.clear();

    // 彈出提示並重定向到登入頁面
    alert('您已成功登出');
    window.location.href = 'login.html';
}
