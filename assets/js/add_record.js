// 加載現有使用者
function loadUsers() {
    fetch('api/getUsers.php')
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                const userSelect = document.getElementById('existingUser');
                data.users.forEach((user) => {
                    const option = document.createElement('option');
                    option.value = JSON.stringify(user);
                    option.textContent = `${user.full_name} - ${user.plate_number}`;
                    userSelect.appendChild(option);
                });
            } else {
                console.error('無法加載使用者:', data.message);
            }
        })
        .catch((error) => console.error('加載使用者失敗:', error));
}

// 選擇使用者後填充數據
document.getElementById('existingUser').addEventListener('change', (e) => {
    const selectedUser = e.target.value ? JSON.parse(e.target.value) : null;
    if (selectedUser) {
        document.getElementById('plateNumber').value = selectedUser.plate_number;
        document.getElementById('phoneNumber').value = selectedUser.phone_number;
    } else {
        document.getElementById('plateNumber').value = '';
        document.getElementById('phoneNumber').value = '';
    }
});

// 提交新增紀錄表單
document.getElementById('addRecordForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = {
        plateNumber: document.getElementById('plateNumber').value.trim(),
        phoneNumber: document.getElementById('phoneNumber').value.trim(),
        repairDate: document.getElementById('repairDate').value,
        description: document.getElementById('description').value.trim(),
        cost: document.getElementById('cost').value.trim(),
    };

    fetch('api/addRecord.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
    })
        .then((response) => response.json())
        .then((data) => {
            const resultMessage = document.getElementById('resultMessage');
            resultMessage.innerHTML = '';

            if (data.success) {
                resultMessage.innerHTML = `<div class="alert alert-success">新增成功！</div>`;
                document.getElementById('addRecordForm').reset();
            } else {
                resultMessage.innerHTML = `<div class="alert alert-danger">新增失敗：${data.message}</div>`;
            }
        })
        .catch((error) => {
            console.error('新增維修紀錄失敗:', error);
            document.getElementById('resultMessage').innerHTML = `<div class="alert alert-danger">系統錯誤，請稍後再試</div>`;
        });
});

document.addEventListener('DOMContentLoaded', loadUsers);
// 登出功能
function logout() {
    localStorage.clear();
    alert('您已成功登出，請重新登入');
    window.location.href = 'login.html';
}