// 步驟 1：檢查用戶名和電子郵件
document.getElementById('step1-form').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
});

// 步驟 2：檢查密碼
document.getElementById('step2-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    if (password !== confirmPassword) {
        alert('密碼與確認密碼不一致');
        return;
    }
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.remove('hidden');
});

// 步驟 3：提交最終註冊表單
document.getElementById('step3-form').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('loading').style.display = 'block';

    const formData = {
        username: document.getElementById('username').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        car_make: document.getElementById('car_make').value,
        plate_number: document.getElementById('plate_number').value,
        engine_number: document.getElementById('engine_number').value,
        year_of_manufacture: document.getElementById('year_of_manufacture').value,
        month_of_manufacture: document.getElementById('month_of_manufacture').value,
        full_name: document.getElementById('full_name').value,
        contact_number: document.getElementById('contact_number').value,
    };

    fetch('api/register.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        if (data.success) {
            alert('註冊成功！請登入');
            window.location.href = 'login.html';
        } else {
            alert('註冊失敗：' + data.message);
        }
    })
    .catch(error => {
        document.getElementById('loading').style.display = 'none';
        console.error('Error:', error);
    });
});

// 顯示服務條款和隱私政策
function showTerms() {
    alert('服務條款: 我們提供的服務是免費的，請遵守相關法律法規。');
}

function showPrivacy() {
    alert('隱私政策: 我們將妥善保護您的個人隱私，信息不會被洩露。');
}
