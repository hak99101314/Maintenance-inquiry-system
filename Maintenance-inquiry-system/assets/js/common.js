function login(username, password) {
    const data = { username: username, password: password };
    ajaxRequest('api/auth/login.php', 'POST', data, function(response) {
        if (response.success) {
            alert("登入成功，角色為：" + response.role);
            window.location.href = response.role === 'admin' ? 'admin_dashboard.jsp' : 'index.html';
        } else {
            alert("登入失敗：" + response.message);
        }
    });
}

function getAppointments(customerId) {
    const data = { customer_id: customerId };
    ajaxRequest('api/appointments/getAppointments.php', 'POST', data, function(appointments) {
        console.log("預約記錄：", appointments);
        // 在頁面顯示查詢結果
    });
}
function ajaxRequest(url, method, data = null, callback) {
    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: data ? JSON.stringify(data) : null
    })
    .then(response => response.json())
    .then(callback)
    .catch(error => console.error('Error:', error));
}
