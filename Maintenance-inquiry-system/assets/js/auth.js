function checkLoginStatus() {
    const user = localStorage.getItem('user_role');
    const navItems = document.getElementById('nav-items');
    navItems.innerHTML = '';

    if (user) {
        navItems.innerHTML += `<li class="nav-item"><a class="nav-link" href="profile.html">個人資料</a></li>`;
        navItems.innerHTML += `<li class="nav-item"><a class="nav-link" href="#" onclick="logout()">登出</a></li>`;
    } else {
        navItems.innerHTML += `<li class="nav-item"><a class="nav-link" href="login.html">登入</a></li>`;
        navItems.innerHTML += `<li class="nav-item"><a class="nav-link" href="register.html">註冊</a></li>`;
    }
}

function logout() {
    localStorage.removeItem('user_role');
    window.location.href = 'index.html';
}

document.addEventListener('DOMContentLoaded', checkLoginStatus);
