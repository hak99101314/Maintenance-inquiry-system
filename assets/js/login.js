// 登入功能
async function login(event) {
    event.preventDefault(); // 防止表單提交刷新頁面

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!username || !password) {
        alert('請輸入帳號和密碼');
        return;
    }

    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        const data = await response.json();

        if (data.success) {
            // 儲存用戶信息到 Session Storage（比 Local Storage 更安全）
            sessionStorage.setItem('user_id', data.user_id);
            sessionStorage.setItem('user_role', data.role);
            sessionStorage.setItem('username', data.username);
            sessionStorage.setItem('full_name', data.full_name);

            // 顯示歡迎訊息
            alert(`歡迎 ${data.full_name} (${data.role === 'admin' ? '管理員' : (data.role === 'staff' ? '員工' : '會員')}) 登入！`);

            // 根據角色重定向
            if (data.role === 'admin') {
                window.location.href = 'admin_dashboard.html';
            } else {
                window.location.href = 'dashboard.html';
            }
        } else {
            alert(data.message || '登入失敗：帳號或密碼錯誤');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('伺服器發生錯誤，請稍後再試');
    }
}

// 忘記密碼處理
function requestPasswordReset(event) {
    event.preventDefault();
    const email = document.getElementById('resetEmail').value;

    fetch('api/forgotPassword.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('重設密碼連結已發送至您的電子郵件');
            const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
            modal.hide();
        } else {
            alert('發送失敗: ' + data.message);
        }
    })
    .catch(err => console.error('錯誤:', err));
}

// 登出功能
function logout() {
    localStorage.clear(); // 清除 Local Storage 資料
    alert('您已成功登出');
    window.location.href = 'login.html';
}
