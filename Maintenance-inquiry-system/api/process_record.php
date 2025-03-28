<?php
// 注意：此檔案內不能有任何多餘空白或換行，請確保 <?php 標籤必須是檔案最前面

ob_start();  // 開啟輸出緩衝區

// 關閉錯誤直接顯示到瀏覽器（開發階段可以開啟以便偵錯，上線時建議關閉）
ini_set('display_errors', 0);
error_reporting(0);

// 設定回傳格式為 JSON
header('Content-Type: application/json');

// 取得前端送來的資料
$record_id = isset($_POST['record_id']) ? trim($_POST['record_id']) : '';
$vehicle_id = isset($_POST['vehicle_id']) ? trim($_POST['vehicle_id']) : '';

// 這裡可加上資料驗證與資料庫儲存的動作
// 以下僅模擬一個成功回傳的範例

$response = [
    'success' => true,
    'message' => '記錄已成功送出！',
    'data' => [
        'record_id'  => $record_id,
        'vehicle_id' => $vehicle_id
    ]
];

// 清空緩衝區，確保沒有多餘的輸出
ob_clean();
echo json_encode($response);
exit;
?>