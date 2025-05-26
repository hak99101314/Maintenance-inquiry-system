document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("register-form").addEventListener("submit", function (event) {
        event.preventDefault();
        registerUser();
    });
});

// ========== 步驟切換驗證 ==========

function nextStep(step) {
    if (step === 1) {
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();

        if (!username || !email) {
            alert("請填寫帳號與電子信箱");
            return;
        }

        // 簡單檢查email格式
        if (!email.includes('@') || !email.includes('.')) {
            alert("請輸入正確的電子郵件格式");
            return;
        }

        // 檢查帳號是否已存在
        fetch("api/check_user.php?username=" + username)
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    alert("帳號已有人使用，請更換");
                } else {
                    // 檢查Email是否已存在
                    fetch("api/check_user.php?email=" + email)
                        .then(response => response.json())
                        .then(data => {
                            if (data.exists) {
                                alert("電子信箱已有人使用，請更換");
                            } else {
                                document.getElementById("step1").classList.add("hidden");
                                document.getElementById("step2").classList.remove("hidden");
                            }
                        });
                }
            });
    } else if (step === 2) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (!password || !confirmPassword) {
            alert("請填寫密碼與確認密碼");
            return;
        }

        if (password !== confirmPassword) {
            alert("密碼與確認密碼不一致");
            return;
        }

        document.getElementById("step2").classList.add("hidden");
        document.getElementById("step3").classList.remove("hidden");
    }
}

// ========== 註冊使用者（步驟3 + 最後送出）==========
function registerUser() {
    // 檢查步驟3欄位
    const carMake = document.getElementById("car_make").value.trim();
    const plateNumber = document.getElementById("plate_number").value.trim();
    const engineNumber = document.getElementById("engine_number").value.trim();
    const year = document.getElementById("year_of_manufacture").value.trim();
    const month = document.getElementById("month_of_manufacture").value.trim();
    const fullName = document.getElementById("full_name").value.trim();
    const contactNumber = document.getElementById("contact_number").value.trim();
    const terms = document.getElementById("terms").checked;

    if (!carMake || !plateNumber || !engineNumber || !year || !month || !fullName || !contactNumber) {
        alert("請完整填寫所有車輛與聯絡資訊");
        return;
    }

    if (!terms) {
        alert("請同意服務條款與隱私政策");
        return;
    }

    // 顯示載入動畫
    document.getElementById("loading").style.display = "block";

    let formData = new FormData(document.getElementById("register-form"));

    fetch("register.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById("loading").style.display = "none";

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
