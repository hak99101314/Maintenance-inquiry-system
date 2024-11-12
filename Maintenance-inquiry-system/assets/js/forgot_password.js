document.getElementById('forgot-password-form').addEventListener('submit', function(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    ajaxRequest('/api/auth/forgot_password.php', 'POST', { email }, function(response) {
        alert(response.success ? "重設密碼連結已發送至您的郵箱" : "錯誤：" + response.message);
    });
});
