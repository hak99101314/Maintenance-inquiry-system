// 載入庫存數據
function loadInventory() {
    fetch('api/getInventory.php')
        .then(response => response.json())
        .then(data => {
            const inventoryTableBody = document.getElementById('inventory-table-body');
            inventoryTableBody.innerHTML = '';
            if (data.success) {
                data.inventory.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.partName}</td>
                        <td>${item.quantity}</td>
                        <td>
                            <button class="btn btn-secondary" onclick="updateInventory('${item.partName}')">更新數量</button>
                            <button class="btn btn-danger" onclick="deleteInventory('${item.partName}')">刪除</button>
                        </td>
                    `;
                    inventoryTableBody.appendChild(row);
                });
            } else {
                alert(data.message || '無法加載庫存數據');
            }
        })
        .catch(error => console.error('無法加載庫存列表:', error));
}

// 更新庫存數量
function updateInventory(partName) {
    const quantity = prompt(`請輸入 ${partName} 的新庫存數量:`);
    if (quantity !== null) {
        fetch('api/updateInventory.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ partName, quantity })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadInventory();
                    alert('庫存已更新');
                } else {
                    alert('更新失敗：' + data.message);
                }
            })
            .catch(error => console.error('更新庫存失敗:', error));
    }
}

// 刪除庫存項目
function deleteInventory(partName) {
    if (confirm(`確定要刪除 ${partName} 嗎？`)) {
        fetch('api/deleteInventory.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ partName })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadInventory();
                    alert('庫存項目已刪除');
                } else {
                    alert('刪除失敗：' + data.message);
                }
            })
            .catch(error => console.error('刪除庫存失敗:', error));
    }
}

// 新增零件
document.getElementById('add-inventory-btn').addEventListener('click', function () {
    const partName = prompt('請輸入零件名稱:');
    const quantity = prompt('請輸入零件數量:');
    if (partName && quantity) {
        fetch('api/addInventory.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ partName, quantity })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadInventory();
                    alert('新增成功');
                } else {
                    alert('新增失敗：' + data.message);
                }
            })
            .catch(error => console.error('新增庫存失敗:', error));
    }
});

document.addEventListener('DOMContentLoaded', loadInventory);
