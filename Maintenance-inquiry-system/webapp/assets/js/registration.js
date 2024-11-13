document.getElementById('step1-form').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.remove('hidden');
});

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

document.getElementById('step3-form').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('loading').style.display = 'block';
    // 提交表單邏輯，例如 AJAX 發送數據
});

function showTerms() {
    alert('服務條款: 我們提供的服務是免費的，請遵守相關法律法規。');
}

function showPrivacy() {
    alert('隱私政策: 我們將妥善保護您的個人隱私，信息不會被洩露。');
}
