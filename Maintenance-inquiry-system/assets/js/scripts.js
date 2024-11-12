// 通用的 fetch 請求處理
function handleFetch(url, options = {}) {
    return fetch(url, options)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .catch(error => console.error('Fetch error:', error));
}

// 加載庫存列表
function loadInventory() {
    handleFetch('/admin/get-inventory')
        .then(data => {
            const inventoryTableBody = document.getElementById('inventory-table-body');
            inventoryTableBody.innerHTML = '';
            data.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.partName}</td>
                    <td>${item.quantity}</td>
                    <td><button class="btn btn-secondary" onclick="updateInventory('${item.partName}')">更新數量</button></td>
                `;
                inventoryTableBody.appendChild(row);
            });
        });
}

// 更新庫存
function updateInventory(partName) {
    const quantity = prompt(`請輸入 ${partName} 的新庫存數量:`);
    if (quantity !== null) {
        handleFetch(`/admin/update-inventory/${partName}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ quantity })
        }).then(data => {
            if (data.success) {
                loadInventory();
                alert('庫存已更新');
            } else {
                alert('更新失敗');
            }
        });
    }
}

// 加載訂單列表
function loadOrders() {
    handleFetch('/admin/get-orders')
        .then(data => {
            const orderTableBody = document.getElementById('order-table-body');
            orderTableBody.innerHTML = '';
            data.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${order.id}</td>
                    <td>${order.username}</td>
                    <td>
                        <select class="form-select" onchange="updateOrderStatus(${order.id}, this.value)">
                            <option value="待維修" ${order.status === '待維修' ? 'selected' : ''}>待維修</option>
                            <option value="維修中" ${order.status === '維修中' ? 'selected' : ''}>維修中</option>
                            <option value="已完成" ${order.status === '已完成' ? 'selected' : ''}>已完成</option>
                        </select>
                    </td>
                    <td><button class="btn btn-danger" onclick="deleteOrder(${order.id})">刪除</button></td>
                `;
                orderTableBody.appendChild(row);
            });
        });
}

// 更新訂單狀態
function updateOrderStatus(orderId, status) {
    handleFetch(`/admin/update-order/${orderId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
    }).then(data => {
        if (data.success) {
            alert('訂單狀態已更新');
        } else {
            alert('更新失敗');
        }
    });
}

// 刪除訂單
function deleteOrder(orderId) {
    handleFetch(`/admin/delete-order/${orderId}`, { method: 'DELETE' })
        .then(data => {
            if (data.success) {
                loadOrders();
                alert('訂單已刪除');
            } else {
                alert('刪除失敗');
            }
        });
}

// 加載用戶列表
function loadUsers() {
    handleFetch('/admin/get-users')
        .then(data => {
            const userTableBody = document.getElementById('user-table-body');
            userTableBody.innerHTML = '';
            data.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.fullName}</td>
                    <td>${user.role}</td>
                    <td>
                        <button class="btn btn-secondary">編輯</button>
                        <button class="btn btn-danger" onclick="deleteUser(${user.id})">刪除</button>
                    </td>
                `;
                userTableBody.appendChild(row);
            });
        });
}

// 刪除用戶
function deleteUser(userId) {
    handleFetch(`/admin/delete-user/${userId}`, { method: 'DELETE' })
        .then(data => {
            if (data.success) {
                loadUsers();
                alert('用戶已刪除');
            } else {
                alert('刪除失敗');
            }
        });
}

// 加載系統設定
function loadSettings() {
    const maxUsers = document.getElementById('max-users').value;
    const backupSchedule = document.getElementById('backup-schedule').value;

    document.getElementById('settings-form').addEventListener('submit', function(e) {
        e.preventDefault();
        handleFetch('/admin/update-settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ maxUsers, backupSchedule })
        }).then(data => {
            if (data.success) {
                alert('設定已更新');
            } else {
                alert('設定更新失敗');
            }
        });
    });
}

// 初始化頁面
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('inventory-table-body')) loadInventory();
    if (document.getElementById('order-table-body')) loadOrders();
    if (document.getElementById('user-table-body')) loadUsers();
    if (document.getElementById('settings-form')) loadSettings();
});
