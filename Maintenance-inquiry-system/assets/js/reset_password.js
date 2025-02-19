function resetPassword() {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    const newPassword = document.getElementById('newPassword').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();

    if (!newPassword || !confirmPassword) {
        alert('請輸入新密碼並確認密碼');
        return;
    }

    if (newPassword !== confirmPassword) {
        alert('密碼不一致，請重新輸入');
        return;
    }

    fetch('api/reset_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, newPassword }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('密碼重設成功！請使用新密碼登入');
            window.location.href = 'login.html';
        } else {
            alert('重設密碼失敗：' + (data.message || "未知錯誤"));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('系統錯誤，請稍後再試');
    });
}
