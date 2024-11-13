document.getElementById('reset-password-form').addEventListener('submit', function(event) {
    event.preventDefault();
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword !== confirmPassword) {
        alert("密碼不匹配");
        return;
    }

    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    ajaxRequest('/api/auth/reset_password.php', 'POST', { token, new_password: newPassword }, function(response) {
        alert(response.success ? "密碼重設成功" : "錯誤：" + response.message);
        if (response.success) window.location.href = "login.html";
    });
});
