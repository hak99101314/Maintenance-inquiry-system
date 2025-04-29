// 登出函式：清除使用者資料(例如 token)並重新導向登入頁面
function logout() {
    // 清除本地存儲中的認證資訊
    localStorage.removeItem('token');
    // 也可以清除其它使用者相關資料，例如：
    // sessionStorage.clear();

    // 導向到登入頁面（請根據實際情況調整路徑）
    window.location.href = 'login.html';

    // 如果希望阻止預設連結行為，可使用：
    // event.preventDefault();
} 