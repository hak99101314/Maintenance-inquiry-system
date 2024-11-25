// 動態生成導航列
document.addEventListener('DOMContentLoaded', function () {
    const navItems = document.getElementById('nav-items');
    const userRole = localStorage.getItem('user_role');
    const userName = localStorage.getItem('username') || '訪客';

    navItems.innerHTML = `
        <li class="nav-item"><a class="nav-link" href="index.html">首頁</a></li>
    `;

    if (userRole === 'admin' || userRole === 'staff') {
        // 後端人員專用功能
        navItems.innerHTML += `
            <li class="nav-item"><a class="nav-link" href="admin_users.html">管理員系統</a></li>
        `;
    } else if (userRole === 'customer') {
        // 會員專用功能
        navItems.innerHTML += `
            <li class="nav-item"><a class="nav-link" href="profile.html">個人資料</a></li>
            <li class="nav-item"><a class="nav-link" href="query.html">查詢維修紀錄</a></li>
            <li class="nav-item"><a class="nav-link" href="appointments.html">預約維修</a></li>
            <li class="nav-item"><a class="nav-link" href="survey.html">滿意度調查</a></li>
        `;
    }

    navItems.innerHTML += `
        <li class="nav-item"><a class="nav-link" href="#" onclick="logout()">登出</a></li>
    `;

    // 顯示歡迎訊息
    document.getElementById('welcome-message').innerText = `歡迎 ${userName}`;
});

// 登出功能
function logout() {
    localStorage.clear();
    alert('您已成功登出');
    window.location.href = 'login.html';
}
