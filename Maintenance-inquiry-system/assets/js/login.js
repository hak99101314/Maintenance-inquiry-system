document.getElementById('login-form').addEventListener('submit', handleLogin);

function handleLogin(event) {
    event.preventDefault();  // 阻止表單提交的默認行為
    const data = {
        username: document.getElementById('username').value,
        password: document.getElementById('password').value
    };
    ajaxRequest('/api/auth/login.php', 'POST', data, function(response) {
        if (response.success) {
            // 存儲用戶角色和名稱到 sessionStorage
            sessionStorage.setItem('user_role', response.role);
            sessionStorage.setItem('user_name', response.username);
            window.location.href = 'dashboard.html'; // 登入成功後跳轉到儀表板
        } else {
            alert('登入失敗: ' + response.message);
        }
    });
}
