// 載入用戶資料
function loadProfile() {
    fetch('api/getProfile.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
        .then((response) => response.json())
        .then((data) => {
            console.log(data); // 檢查伺服器回應
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
            }
        })
        .catch((error) => {
            console.error('無法載入用戶資料:', error);
        });
}

// 保存用戶資料
function saveProfile(event) {
    event.preventDefault();

    const profileData = {
        full_name: document.getElementById('full_name').value,
        email: document.getElementById('email').value,
        contact_number: document.getElementById('contact_number').value,
        license_plate: document.getElementById('license_plate').value,
        brand: document.getElementById('brand').value,
        model: document.getElementById('model').value,
    };

    const passwordData = {
        current_password: document.getElementById('current_password').value,
        new_password: document.getElementById('new_password').value,
        confirm_new_password: document.getElementById('confirm_new_password').value,
    };

    // 發送用戶資料更新請求
    fetch('api/updateProfile.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(profileData),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert('用戶資料更新成功');
            } else {
                alert('用戶資料更新失敗：' + data.message);
            }
        })
        .catch((error) => {
            console.error('用戶資料更新錯誤:', error);
        });

    // 發送密碼更新請求
    if (passwordData.current_password && passwordData.new_password && passwordData.confirm_new_password) {
        if (passwordData.new_password !== passwordData.confirm_new_password) {
            alert('新密碼與確認密碼不一致');
            return;
        }

        fetch('api/updatePassword.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(passwordData),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert('密碼更新成功');
                } else {
                    alert('密碼更新失敗：' + data.message);
                }
            })
            .catch((error) => {
                console.error('密碼更新錯誤:', error);
            });
    }
}

document.getElementById('profile-form').addEventListener('submit', saveProfile);
document.addEventListener('DOMContentLoaded', loadProfile);
