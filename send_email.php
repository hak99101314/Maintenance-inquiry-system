<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 載入 Composer autoload
require 'vendor/autoload.php';

function sendEmail($to_email, $to_name, $subject, $body_html) {
   /** @var \PHPMailer\PHPMailer\PHPMailer $mail */
$mail = new PHPMailer(true);

    try {
        // 基本設定
        $mail->CharSet = 'UTF-8';  // 設定UTF-8，防止中文亂碼
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'c121789581@gmail.com';  // 👈 改成你自己的
        $mail->Password = 'hwrtwkkamykuvrnd';          // 👈 改成你自己的
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // 發信人 & 收件人
        $mail->setFrom('tf899090@gmail.com', '睿煬企業社');
        $mail->addAddress($to_email, $to_name);

        // 內嵌LOGO圖片
        $mail->addEmbeddedImage(__DIR__.'/Screenshot_20250224_162218_Drive.jpg', 'companylogo');

        // 設定內容是HTML格式
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // 寄送內容，插入防下載 Logo
        $mail->Body = "
            <div style='text-align:center;'>
                <img src='cid:companylogo' style='width:200px; pointer-events: none;' oncontextmenu='return false;' alt='公司Logo'>
            </div>
            <div style='font-family:Arial, sans-serif; font-size:15px; margin-top:20px;'>
                $body_html
            </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        echo '寄信失敗，錯誤訊息：' . $mail->ErrorInfo;
        return false;
    }
}
?>
