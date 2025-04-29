document.addEventListener('DOMContentLoaded', function () {
    const userRole = localStorage.getItem('user_role'); // 從 Local Storage 獲取角色
    const roleTitle = document.getElementById('role-title');
    const roleFeatures = document.getElementById('role-features');
    const navItems = document.getElementById('nav-items');

    if (!userRole) {
        alert('您尚未登入，請先登入');
        window.location.href = 'login.html';
        return;
    }

    // 共用導航列
    navItems.innerHTML = `
        <li class="nav-item"><a class="nav-link" href="index.html">首頁</a></li>
        <li class="nav-item"><a class="nav-link" href="#" onclick="logout()">登出</a></li>
    `;

    // 根據角色動態顯示功能
    switch (userRole) {
        case 'admin':
            roleTitle.innerText = '管理員功能';
            roleFeatures.innerHTML = `
                <div class="col-md-4"><a href="admin_users.html" class="btn btn-primary w-100 mb-3">用戶管理</a></div>
                <div class="col-md-4"><a href="admin_inventory.html" class="btn btn-primary w-100 mb-3">庫存管理</a></div>
                <div class="col-md-4"><a href="admin_orders.html" class="btn btn-primary w-100 mb-3">維修狀態管理</a></div>
                <div class="col-md-4"><a href="admin_settings.html" class="btn btn-primary w-100 mb-3">系統設定</a></div>
            `;
            break;

        case 'staff':
            roleTitle.innerText = '員工功能';
            roleFeatures.innerHTML = `
            <div class="col-md-4"><a href="appointment_records.html" class="btn btn-primary w-100 mb-3">預約審核</a></div>
            <div class="col-md-4"><a href="add_maintenance.html" class="btn btn-primary w-100 mb-3">新增維修紀錄</a></div>
            <div class="col-md-4"><a href="admin_inventory.html" class="btn btn-primary w-100 mb-3">零件進貨管理</a></div>
            <div class="col-md-4"><a href="admin_inventory.html" class="btn btn-primary w-100 mb-3">庫存管理</a></div>
            <div class="col-md-4"><a href="admin_orders.html" class="btn btn-primary w-100 mb-3">維修狀態處理</a></div>
            `;
            break;

        case 'customer':
            roleTitle.innerText = '會員功能';
            roleFeatures.innerHTML = `
                <div class="col-md-4"><a href="index.html" class="btn btn-primary w-100 mb-3">首頁</a></div>
                <div class="col-md-4"><a href="profile.html" class="btn btn-primary w-100 mb-3">個人資料管理</a></div>
                <div class="col-md-4"><a href="appointments.html" class="btn btn-primary w-100 mb-3">預約維修</a></div>
                <div class="col-md-4"><a href="query.html" class="btn btn-primary w-100 mb-3">查詢維修紀錄</a></div>
                <div class="col-md-4"><a href="survey.html" class="btn btn-primary w-100 mb-3">滿意度調查</a></div>
            `;
            break;

        default:
            alert('未授權的角色，請重新登入');
            localStorage.clear();
            window.location.href = 'login.html';
            break;
    }
});

// 登出功能
function logout() {
    localStorage.clear();
    alert('您已成功登出');
    window.location.href = 'login.html';
}
