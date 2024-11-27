// 加載用戶列表
function loadUsers() {
    fetch('api/getUsers.php')
        .then(response => response.json())
        .then(data => {
            console.log('API 響應:', data); // 調試輸出
            if (data.success) {
                const userTableBody = document.getElementById('user-table-body');
                userTableBody.innerHTML = '';
                data.data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.user_id}</td>
                        <td>${user.username}</td>
                        <td>${user.full_name}</td>
                        <td>${user.role}</td>
                        <td>
                            <button class="btn btn-secondary btn-sm" onclick="editUser(${user.user_id})">編輯</button>
                            <button class="btn btn-danger btn-sm" onclick="deleteUser(${user.user_id})">刪除</button>
                        </td>
                    `;
                    userTableBody.appendChild(row);
                });
            } else {
                alert('無法加載用戶列表：' + data.message);
            }
        })
        .catch(error => console.error('無法加載用戶列表:', error));
}

// 編輯用戶
function editUser(userId) {
    // 顯示模態框
    const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    editUserModal.show();

    // 從後端加載用戶數據
    fetch(`api/getUserDetails.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.data;

                // 填充用戶數據到模態框
                document.getElementById('edit-username').value = user.username;
                document.getElementById('edit-full-name').value = user.full_name;
                document.getElementById('edit-role').value = user.role;
                document.getElementById('edit-user-id').value = user.user_id; // 隱藏字段存儲 user_id
            } else {
                alert(data.message || '無法加載用戶數據');
            }
        })
        .catch(error => {
            console.error('加載用戶數據時出錯:', error);
            alert('無法加載用戶數據');
        });
}


// 保存用戶修改
document.getElementById('edit-user-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const updatedUser = {
        username: document.getElementById('edit-username').value,
        full_name: document.getElementById('edit-full-name').value,
        role: document.getElementById('edit-role').value,
        password: document.getElementById('edit-password').value.trim(), // 可選
    };

    // 發送更新請求到後端
    fetch('api/updateUser.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(updatedUser),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('用戶更新成功');
                const editUserModal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                editUserModal.hide();
                loadUsers(); // 重新加載用戶列表
            } else {
                alert(data.message || '用戶更新失敗');
            }
        })
        .catch(error => console.error('更新用戶時出錯:', error));
});

// 新增用戶邏輯
document.getElementById('add-user-btn').addEventListener('click', function () {
    const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
    addUserModal.show();
});

document.getElementById('add-user-form').addEventListener('submit', function (event) {
    event.preventDefault();
    const newUser = {
        username: document.getElementById('new-username').value,
        full_name: document.getElementById('new-full-name').value,
        role: document.getElementById('new-role').value,
        password: document.getElementById('new-password').value,
    };

    fetch('api/addUser.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newUser),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert('新增用戶成功');
                loadUsers(); // 重新載入用戶列表
            } else {
                alert('新增用戶失敗：' + data.message);
            }
        })
        .catch((error) => console.error('新增用戶錯誤:', error));
});
// 刪除用戶
function deleteUser(userId) {
    if (confirm(`確定刪除用戶ID：${userId}？`)) {
        fetch('api/deleteUser.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('用戶已成功刪除');
                    location.reload(); // 刷新頁面
                } else {
                    alert(data.message || '刪除用戶失敗');
                }
            })
            .catch(error => {
                console.error('刪除用戶時出錯:', error);
                alert('伺服器發生錯誤，請稍後再試');
            });
    }
}

// 初始化
document.addEventListener('DOMContentLoaded', loadUsers);
