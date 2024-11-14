function login() {
    console.log('Login script loaded'); // 檢查 login.js 是否加載成功
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    fetch('api/login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username, password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            localStorage.setItem('user_role', data.user_role);
            localStorage.setItem('username', data.username);

            // 判別用戶角色並重定向
            if (data.user_role === 'admin' || data.user_role === 'staff') {
                window.location.href = 'admin_users.html';
            } else {
                window.location.href = 'index.html';
            }
        } else {
            alert('登入失敗：帳號或密碼錯誤');
        }
    })
    .catch(error => console.error('Error:', error));
}

function showForgotPasswordModal() {
    document.getElementById('forgotPasswordModal').style.display = 'block';
}

function closeForgotPasswordModal() {
    document.getElementById('forgotPasswordModal').style.display = 'none';
}

function requestPasswordReset() {
    const email = document.getElementById('resetEmail').value;

    fetch('api/requestPasswordReset.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('重設密碼的連結已發送至您的電子郵件');
            closeForgotPasswordModal();
        } else {
            alert('未找到此電子郵件地址');
        }
    })
    .catch(error => console.error('Error:', error));
}
