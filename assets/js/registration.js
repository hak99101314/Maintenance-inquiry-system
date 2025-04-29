document.addEventListener("DOMContentLoaded", function () {
    // 綁定表單提交事件
    document.getElementById("register-form").addEventListener("submit", function (event) {
        event.preventDefault(); // 防止預設提交行為
        registerUser();
    });
});

// 註冊使用者函式
function registerUser() {
    // 取得表單資料
    let formData = new FormData(document.getElementById("register-form"));

    // 顯示載入動畫
    document.getElementById("loading").style.display = "block";

    fetch("register.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json()) // 解析 JSON 回應
    .then(data => {
        document.getElementById("loading").style.display = "none"; // 隱藏載入動畫

        if (data.success) {
            alert("註冊成功！即將跳轉至登入頁面...");
            window.location.href = "login.php?register=success";
        } else {
            alert("註冊失敗：" + data.message);
        }
    })
    .catch(error => {
        document.getElementById("loading").style.display = "none";
        alert("發生錯誤，請稍後再試！");
        console.error("錯誤訊息:", error);
    });
}
