document.getElementById("appointment-form")?.addEventListener("submit", function (e) {
    e.preventDefault();

    const service = document.getElementById("service").value.trim();
    const date = document.getElementById("date").value;
    const time = document.getElementById("time").value;

    if (!service || !date || !time) {
        alert('請完整填寫所有欄位');
        return;
    }

    fetch("api/createAppointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ service, date, time }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert("預約成功！");
                window.location.href = "appointment_records.html";
            } else {
                alert("預約失敗：" + (data.message || "未知錯誤"));
            }
        })
        .catch((error) => {
            console.error("預約失敗:", error);
            alert("系統錯誤，請稍後再試");
        });
});
