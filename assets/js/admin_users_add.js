document.getElementById('add-user-form').addEventListener('submit', async (event) => {
    event.preventDefault();

    const formData = {
        username: document.getElementById('username').value.trim(),
        full_name: document.getElementById('full_name').value.trim(),
        email: document.getElementById('email').value.trim(),
        role: document.getElementById('role').value,
        password: document.getElementById('password').value.trim(),
    };

    try {
        const response = await fetch('api/add_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData),
        });

        const data = await response.json();
        if (data.success) {
            alert('用戶新增成功！');
            window.location.href = 'admin_users.html'; // 返回用戶管理頁面
        } else {
            alert('新增用戶失敗：' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('伺服器發生錯誤，請稍後再試');
    }
});

// 登出功能
function logout() {
    localStorage.clear();
    alert('您已成功登出');
    window.location.href = 'login.html';
}
