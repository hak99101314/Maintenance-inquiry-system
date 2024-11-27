document.getElementById("appointment-form").addEventListener("submit", function (e) {
    e.preventDefault();

    const service = document.getElementById("service").value;
    const date = document.getElementById("date").value;
    const time = document.getElementById("time").value;

    fetch("api/createAppointment.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ service, date, time }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert("預約成功！");
                window.location.href = "appointment_records.html"; // 跳轉到預約紀錄頁面
            } else {
                alert("預約失敗：" + data.message);
            }
        })
        .catch((error) => console.error("預約失敗:", error));
});
