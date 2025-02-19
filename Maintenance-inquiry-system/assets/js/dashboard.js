document.addEventListener('DOMContentLoaded', function () {
    const userRole = localStorage.getItem('user_role');
    const roleTitle = document.getElementById('role-title');
    const roleFeatures = document.getElementById('role-features');
    const navItems = document.getElementById('nav-items');

    if (!userRole) {
        alert('您尚未登入，請先登入');
        window.location.href = 'login.html';
        return;
    }

    // 共用功能導航列
    navItems.innerHTML = `
        <li class="nav-item"><a class="nav-link" href="admin_dashboard.html">首頁</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="logout()">登出</a></li>
    `;

    if (userRole === 'admin') {
        roleTitle.innerText = '管理員功能';
        roleFeatures.innerHTML = `
            <div class="col-md-4"><a href="admin_users.html" class="btn btn-primary w-100 mb-3">用戶管理</a></div>
            <div class="col-md-4"><a href="admin_inventory.html" class="btn btn-primary w-100 mb-3">庫存管理</a></div>
            <div class="col-md-4"><a href="admin_orders.html" class="btn btn-primary w-100 mb-3">訂單管理</a></div>
            <div class="col-md-4"><a href="admin_settings.html" class="btn btn-primary w-100 mb-3">系統設定</a></div>
        `;
    } else if (userRole === 'staff') {
        roleTitle.innerText = '員工功能';
        roleFeatures.innerHTML = `
            <div class="col-md-4"><a href="appointment_records.html" class="btn btn-primary w-100 mb-3">預約審核</a></div>
            <div class="col-md-4"><a href="admin_inventory.html" class="btn btn-primary w-100 mb-3">零件進貨管理</a></div>
            <div class="col-md-4"><a href="admin_orders.html" class="btn btn-primary w-100 mb-3">訂單處理</a></div>
        `;
    } else if (userRole === 'customer') {
        alert('無權進入此頁面，請重新登入');
        window.location.href = 'index.html';
    } else {
        alert('未授權的角色，請重新登入');
        window.location.href = 'login.html';
    }
});

// 登出功能
function logout() {
    localStorage.clear();
    alert('您已成功登出');
    window.location.href = 'login.html';
}
