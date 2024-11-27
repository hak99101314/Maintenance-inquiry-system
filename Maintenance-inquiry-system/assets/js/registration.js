document.addEventListener('DOMContentLoaded', () => {
    const step1Next = document.getElementById('step1-next');
    const step2Next = document.getElementById('step2-next');
    const step2Prev = document.getElementById('step2-prev');
    const step3Prev = document.getElementById('step3-prev');

    // 下一步: 第1步到第2步
    step1Next.addEventListener('click', () => {
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const fullName = document.getElementById('full_name').value.trim();
        const contactNumber = document.getElementById('contact_number').value.trim();

        if (username && email && fullName && contactNumber) {
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
        } else {
            alert('請填寫所有欄位');
        }
    });

    // 下一步: 第2步到第3步
    step2Next.addEventListener('click', () => {
        const password = document.getElementById('password').value.trim();
        const confirmPassword = document.getElementById('confirm_password').value.trim();

        if (password && confirmPassword && password === confirmPassword) {
            document.getElementById('step2').classList.add('hidden');
            document.getElementById('step3').classList.remove('hidden');
        } else {
            alert('密碼與確認密碼不一致');
        }
    });

    // 上一步: 第2步到第1步
    step2Prev.addEventListener('click', () => {
        document.getElementById('step2').classList.add('hidden');
        document.getElementById('step1').classList.remove('hidden');
    });

    // 上一步: 第3步到第2步
    step3Prev.addEventListener('click', () => {
        document.getElementById('step3').classList.add('hidden');
        document.getElementById('step2').classList.remove('hidden');
    });

    // 提交表單: 第3步
    document.getElementById('step3-form').addEventListener('submit', (e) => {
        e.preventDefault();
        document.getElementById('loading').classList.remove('hidden');

        const formData = {
            username: document.getElementById('username').value.trim(),
            email: document.getElementById('email').value.trim(),
            full_name: document.getElementById('full_name').value.trim(),
            contact_number: document.getElementById('contact_number').value.trim(),
            password: document.getElementById('password').value.trim(),
            car_make: document.getElementById('car_make').value.trim(),
            plate_number: document.getElementById('plate_number').value.trim(),
            engine_number: document.getElementById('engine_number').value.trim(),
        };

        fetch('api/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData),
        })
            .then((response) => response.json())
            .then((data) => {
                document.getElementById('loading').classList.add('hidden');
                if (data.success) {
                    alert('註冊成功！');
                    window.location.href = 'login.html';
                } else {
                    alert('註冊失敗：' + data.message);
                }
            })
            .catch((error) => {
                document.getElementById('loading').classList.add('hidden');
                console.error('Error:', error);
                alert('伺服器發生錯誤，請稍後再試');
            });
    });
});
