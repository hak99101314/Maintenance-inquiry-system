document.getElementById('settings-form').addEventListener('submit', function (e) {
    e.preventDefault();

    // 獲取表單值
    const maxUsers = document.getElementById('max-users').value;
    const backupSchedule = document.getElementById('backup-schedule').value;

    // 發送設定更新請求
    fetch('api/updateSettings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ maxUsers, backupSchedule }),
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('設定已成功更新');
            } else {
                alert('設定更新失敗：' + (data.message || '請稍後再試'));
            }
        })
        .catch(error => {
            console.error('設定更新錯誤:', error);
            alert('伺服器發生錯誤，請稍後再試');
        });
});

// 當頁面載入時獲取當前設定
document.addEventListener('DOMContentLoaded', function () {
    fetch('api/getSettings.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('max-users').value = data.settings.maxUsers;
                document.getElementById('backup-schedule').value = data.settings.backupSchedule;
            } else {
                console.error('無法加載當前設定:', data.message || '未知錯誤');
            }
        })
        .catch(error => console.error('加載設定時出錯:', error));
});
