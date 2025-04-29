document.addEventListener("DOMContentLoaded", function () {
    console.log("✅ DOM 加載完成，開始監聽車牌輸入框...");

    document.querySelector('[name="plate_number"]').addEventListener('change', async function () {
        const plateNumber = this.value.trim();

        if (plateNumber === '') {
            Swal.fire({
                title: '提示',
                text: '請輸入有效的車牌號碼',
                icon: 'warning',
                confirmButtonColor: '#1a4f95'
            });
            return;
        }

        try {
            const apiUrl = `http://localhost/Maintenance-inquiry-system/api/get_vehicle_info.php?plate_number=${encodeURIComponent(plateNumber)}`;
            console.log(`🔍 發送請求到 API: ${apiUrl}`);

            const response = await fetch(apiUrl);
            const text = await response.text();
            console.log("🔵 API 原始回應:", text); // 這行可以讓你看到 API 真的回傳了什麼

            let result;
            try {
                result = JSON.parse(text);
            } catch (jsonError) {
                console.error("❌ JSON 解析失敗:", jsonError);
                Swal.fire({
                    title: '錯誤',
                    text: 'API 回傳的格式不正確，請檢查伺服器。',
                    icon: 'error',
                    confirmButtonColor: '#1a4f95'
                });
                return;
            }

            console.log("🟢 API 解析後的 JSON:", result);

            if (result.success) {
                document.querySelector('[name="owner"]').value = result.data.owner || '';
                document.querySelector('[name="model"]').value = result.data.model || '';
                document.querySelector('[name="year"]').value = result.data.year || '';

                const contactNumber = result.data.phone ? result.data.phone.trim() : '';

                if (contactNumber.startsWith('02')) {
                    document.querySelector('[name="phone"]').value = contactNumber;
                    document.querySelector('[name="mobile"]').value = '';
                } else if (contactNumber.startsWith('09')) {
                    document.querySelector('[name="mobile"]').value = contactNumber;
                    document.querySelector('[name="phone"]').value = '';
                } else {
                    document.querySelector('[name="phone"]').value = contactNumber;
                    document.querySelector('[name="mobile"]').value = '';
                }
            } else {
                Swal.fire({
                    title: '未找到車輛',
                    text: result.message || '該車牌號碼未登錄，請手動填寫',
                    icon: 'info',
                    confirmButtonColor: '#1a4f95'
                });
                clearVehicleFields();
            }
        } catch (error) {
            console.error("❌ Fetch error:", error.message);
            Swal.fire({
                title: '錯誤',
                text: '無法獲取車輛資訊，請稍後再試',
                icon: 'error',
                confirmButtonColor: '#1a4f95'
            });
            clearVehicleFields();
        }
    });
});
