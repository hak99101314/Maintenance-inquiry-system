// 加載系統設定
function loadSettings() {
    const form = document.getElementById('settings-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const maxUsers = document.getElementById('max-users').value;
            const backupSchedule = document.getElementById('backup-schedule').value;
            handleFetch('/admin/update-settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ maxUsers, backupSchedule })
            })
            .then(data => {
                if (data.success) {
                    alert('設定已更新');
                } else {
                    alert('設定更新失敗');
                }
            })
            .catch(error => {
                console.error('更新設定時出錯:', error);
                alert('系統錯誤，請稍後再試');
            });
        });
    }
}
