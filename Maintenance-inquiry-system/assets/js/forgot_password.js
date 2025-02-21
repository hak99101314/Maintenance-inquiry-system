const form = document.getElementById('forgot-password-form');
if (form) {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const email = document.getElementById('email').value;
        if (!email) {
            alert('請輸入電子郵件');
            return;
        }
        ajaxRequest('/api/auth/forgot_password.php', 'POST', { email }, function(response) {
            alert(response.success ? "重設密碼連結已發送至您的郵箱" : "錯誤：" + (response.message || "未知錯誤"));
        });
    });
}
