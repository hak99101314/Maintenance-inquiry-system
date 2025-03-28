document.getElementById('query-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const plateNumber = document.getElementById('plateNumber').value.trim();
    const phoneNumber = document.getElementById('phoneNumber').value.trim();

    // 清空表格內容並隱藏結果區域
    const resultsContainer = document.getElementById('results-container');
    const resultsTableBody = document.getElementById('results-table-body');
    resultsTableBody.innerHTML = '';
    resultsContainer.classList.add('hidden');

    fetch('api/queryMaintenance.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ plateNumber, phoneNumber })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultsContainer.classList.remove('hidden'); // 顯示結果表格
                data.records.forEach(record => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${record.date}</td>
                        <td>${record.type}</td>
                        <td>${record.content}</td>
                        <td>${record.cost}</td>
                    `;
                    resultsTableBody.appendChild(row);
                });
            } else {
                resultsContainer.classList.remove('hidden'); // 顯示空結果訊息
                resultsTableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-danger text-center">${data.message || '未找到維修紀錄'}</td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('查詢錯誤:', error);
            alert('伺服器錯誤，請稍後再試');
        });
});

// 登出功能
function logout() {
    localStorage.clear();
    alert('您已成功登出');
    window.location.href = 'login.html';
}
