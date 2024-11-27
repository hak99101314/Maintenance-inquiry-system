function loadAppointments() {
    fetch("api/getAppointments.php")
        .then((response) => response.json())
        .then((data) => {
            const recordsBody = document.getElementById("appointment-records-body");
            recordsBody.innerHTML = "";

            if (data.success) {
                data.appointments.forEach((appointment) => {
                    const row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${appointment.service}</td>
                        <td>${appointment.date}</td>
                        <td>${appointment.time}</td>
                    `;
                    recordsBody.appendChild(row);
                });
            } else {
                recordsBody.innerHTML = `<tr><td colspan="3">沒有預約紀錄</td></tr>`;
            }
        })
        .catch((error) => console.error("無法加載預約紀錄:", error));
}

document.addEventListener("DOMContentLoaded", loadAppointments);
