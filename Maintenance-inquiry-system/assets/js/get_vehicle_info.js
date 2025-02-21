// 當車牌號碼輸入框發生變更時觸發事件
document.querySelector('[name="plate_number"]').addEventListener('change', async function () {
    const plateNumber = this.value.trim();

    // 如果車牌號碼為空
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
        // 發送 AJAX 請求到後端 API
        const response = await fetch(`api/get_vehicle_info.php?plate_number=${encodeURIComponent(plateNumber)}`);
        const result = await response.json();

        // 如果請求成功且有資料
        if (response.ok && result.success) {
            document.querySelector('[name="owner"]').value = result.data.owner || '';
            document.querySelector('[name="phone"]').value = result.data.phone || '';
            document.querySelector('[name="car_model"]').value = result.data.car_model || '';
            document.querySelector('[name="year"]').value = result.data.year || '';
        } else {
            // 未找到車輛資訊
            Swal.fire({
                title: '未找到車輛',
                text: result.message || '該車牌號碼未登錄，請手動填寫',
                icon: 'info',
                confirmButtonColor: '#1a4f95'
            });
            clearVehicleFields();
        }
    } catch (error) {
        // 處理伺服器或網路錯誤
        Swal.fire({
            title: '錯誤',
            text: '無法獲取車輛資訊，請稍後再試',
            icon: 'error',
            confirmButtonColor: '#1a4f95'
        });
        clearVehicleFields();
    }
});

// 清空車輛欄位資料
function clearVehicleFields() {
    document.querySelector('[name="owner"]').value = '';
    document.querySelector('[name="phone"]').value = '';
    document.querySelector('[name="car_model"]').value = '';
    document.querySelector('[name="year"]').value = '';
}
