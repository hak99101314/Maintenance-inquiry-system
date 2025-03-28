// 載入所有使用者到下拉選單
document.addEventListener('DOMContentLoaded', function () {
    const userSelect = document.getElementById('userSelect');

    fetch('api/getUsers.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.user_id;
                    option.textContent = `${user.full_name} (${user.username})`;
                    userSelect.appendChild(option);
                });
            } else {
                alert('無法載入使用者: ' + data.message);
            }
        })
        .catch(error => console.error('錯誤:', error));
});

// 當文件載入完成時設置今天日期
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('repairDate').valueAsDate = new Date();
});

// 表單提交處理
document.getElementById('add-maintenance-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        // 收集表單數據
        const maintenanceData = {
            vehicle_id: document.getElementById('vehicleId').value.toUpperCase(),
            date: document.getElementById('repairDate').value,
            type: document.getElementById('maintenanceType').value,
            content: document.getElementById('repairContent').value,
            items: document.getElementById('repairItems').value,
            cost: parseFloat(document.getElementById('repairCost').value),
            staff_id: document.getElementById('staffId').value
        };

        // 發送到後端 API
        const response = await fetch('api/maintenance/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(maintenanceData)
        });

        const result = await response.json();

        if (response.ok) {
            // 顯示成功訊息
            Swal.fire({
                title: '新增成功！',
                text: `維修紀錄已成功儲存\n紀錄編號：${result.record_id}`,
                icon: 'success',
                confirmButtonText: '確定'
            }).then((result) => {
                if (result.isConfirmed) {
                    // 清空表單
                    document.getElementById('add-maintenance-form').reset();
                    // 重新設置日期為今天
                    document.getElementById('repairDate').valueAsDate = new Date();
                }
            });
        } else {
            throw new Error(result.message || '新增維修紀錄失敗');
        }
        
    } catch (error) {
        // 顯示錯誤訊息
        Swal.fire({
            title: '錯誤！',
            text: error.message,
            icon: 'error',
            confirmButtonText: '確定'
        });
    }
});

// 數據驗證函數
function validateMaintenanceData(data) {
    if (!data.vehicle_id) {
        showAlert('警告', '請輸入車輛編號');
        return false;
    }
    
    if (!data.type) {
        showAlert('警告', '請選擇維修類型');
        return false;
    }
    
    if (!data.date) {
        showAlert('警告', '請選擇維修日期');
        return false;
    }
    
    if (!data.content.trim()) {
        showAlert('警告', '請輸入維修內容');
        return false;
    }
    
    if (!data.items.trim()) {
        showAlert('警告', '請輸入維修項目');
        return false;
    }
    
    if (isNaN(data.cost) || data.cost <= 0) {
        showAlert('警告', '請輸入有效的維修費用');
        return false;
    }
    
    if (!data.staff_id) {
        showAlert('警告', '請輸入員工編號');
        return false;
    }
    
    return true;
}

// 顯示提示訊息函數
function showAlert(title, message) {
    Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        confirmButtonText: '確定'
    });
}

// 登出功能
function logout() {
    localStorage.clear();
    alert('您已成功登出');
    window.location.href = 'login.html';
}
