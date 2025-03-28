// 載入用戶資料
function loadProfile() {
    fetch('api/getProfile.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'include' // 確保包含 Cookies
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                const { username, full_name, email, contact_number, license_plate, brand, model } = data.data;

                // 填充用戶資料
                document.getElementById('username').value = username;
                document.getElementById('full_name').value = full_name;
                document.getElementById('email').value = email;
                document.getElementById('contact_number').value = contact_number;

                // 填充車輛資訊
                document.getElementById('license_plate').value = license_plate;
                document.getElementById('brand').value = brand;
                document.getElementById('model').value = model;
            } else {
                alert(data.message || '無法載入用戶資料');
                window.location.href = 'login.html'; // 跳轉到登入頁面
            }
        })
        .catch((error) => {
            console.error('無法載入用戶資料:', error);
        });
}

// 保存用戶資料
function saveProfile(event) {
    event.preventDefault();
    console.log("保存按鈕被點擊"); // 測試是否執行到此

    const profileData = {
        full_name: document.getElementById('full_name').value,
        email: document.getElementById('email').value,
        contact_number: document.getElementById('contact_number').value,
        license_plate: document.getElementById('license_plate').value,
        brand: document.getElementById('brand').value,
        model: document.getElementById('model').value,
    };

    console.log("發送的資料：", profileData); // 檢查發送的數據是否正確


    fetch('api/updateProfile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'include', // 確保包含 Cookies
        body: JSON.stringify(profileData),
    })
    .then((response) => {
        console.log("伺服器回應：", response); // 確認伺服器是否有回應
        return response.json();
    })
    .then((data) => {
        console.log("解析後的回應資料：", data); // 確認伺服器回應內容
        if (data.success) {
            alert('用戶資料更新成功');
            location.reload(); // 刷新頁面
        } else {
            alert('用戶資料更新失敗：' + data.message);
        }
    })
    .catch((error) => {
        console.error('用戶資料更新錯誤:', error);
        alert('伺服器錯誤，請稍後再試');
    });
}

document.getElementById('profile-form').addEventListener('submit', saveProfile);
document.addEventListener('DOMContentLoaded', loadProfile);
// 登出功能
function logout() {
    localStorage.clear();
    alert('您已成功登出，請重新登入');
    window.location.href = 'login.html';
}