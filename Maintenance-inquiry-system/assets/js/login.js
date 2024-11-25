// 登入功能
async function login(event) {
    event.preventDefault(); // 防止表單提交刷新頁面

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    try {
        const response = await fetch('api/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        const data = await response.json();

        if (data.success) {
            // 儲存用戶信息
            localStorage.setItem('user_role', data.user_role);
            localStorage.setItem('username', data.username);
            localStorage.setItem('full_name', data.full_name);
            // 顯示歡迎訊息
            alert(`歡迎 ${data.full_name} (${data.user_role === 'admin' ? '管理員' : (data.user_role === 'staff' ? '員工' : '會員')}) 登入！`);

            // 判別用戶角色並重定向
            if (data.user_role === 'admin' || data.user_role === 'staff') {
                window.location.href = 'admin_users.html';
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

// 密碼重設功能
async function requestPasswordReset(event) {
    event.preventDefault(); // 防止表單提交刷新頁面

    const email = document.getElementById('resetEmail').value;

    try {
        const response = await fetch('api/requestPasswordReset.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });

        const data = await response.json();

        if (data.success) {
            alert('重設密碼的連結已發送至您的電子郵件');
            const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
            modal.hide();
        } else {
            alert(data.message || '未找到此電子郵件地址');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('伺服器發生錯誤，請稍後再試');
    }
}
