<?php
// 引入發信功能
require 'send_email.php';

// 測試收件者資料
$to_email = 'cx930914@gmail.com';  // 👈 改成你自己的信箱
$to_name = '測試收件者';
$subject = '這是測試信件 - 睿煬企業社';
$body_html = "
    親愛的測試用戶您好，<br><br>
    這是一封系統自動發送的測試郵件。<br>
    如果您看到這封信，代表系統寄信功能正常運作！<br><br>
    感謝使用！<br><br>
    睿煬企業社
";

// 呼叫寄信
if (sendEmail($to_email, $to_name, $subject, $body_html)) {
    echo "✅ 測試信寄出成功！請到信箱查看！";
} else {
    echo "❌ 測試信寄出失敗，請檢查設定。";
}
?>
