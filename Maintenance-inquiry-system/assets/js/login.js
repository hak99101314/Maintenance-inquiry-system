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
            // 儲存用戶信息到 Local Storage
            localStorage.setItem('user_id', data.user_id); // 儲存用戶 ID
            localStorage.setItem('user_role', data.user_role); // 儲存角色
            localStorage.setItem('username', data.username); //儲存帳號
            localStorage.setItem('full_name', data.full_name); // 儲存姓名 

            // 顯示歡迎訊息
            alert(`歡迎 ${data.full_name} (${data.user_role === 'admin' ? '管理員' : (data.user_role === 'staff' ? '員工' : '會員')}) 登入！`);

            // 根據角色進行重定向
            if (data.user_role === 'admin' || data.user_role === 'staff') {
                window.location.href = 'admin_dashboard.html';
            } else {
                window.location.href = 'index.html';
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
