function resetPassword() {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');  // 從 URL 中取得 token
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword !== confirmPassword) {
        alert('密碼不一致，請重新輸入');
        return;
    }

    fetch('api/reset_password.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ token, newPassword })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('密碼重設成功！請使用新密碼登入');
            window.location.href = 'login.html';
        } else {
            alert('重設密碼失敗：' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}
