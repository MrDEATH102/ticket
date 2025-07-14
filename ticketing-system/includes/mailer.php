<?php
require_once __DIR__ . '/src/PHPMailer.php';
require_once __DIR__ . '/src/SMTP.php';
require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/config_mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to, $subject, $htmlMessage, $plainMessage = '') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;
        $mail->AltBody = $plainMessage ?: strip_tags($htmlMessage);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function send_password_reset_email($to, $reset_link) {
    $subject = 'بازیابی رمز عبور – elaico';
    $html = "<p>برای بازیابی رمز عبور خود روی لینک زیر کلیک کنید:</p><p><a href='$reset_link'>$reset_link</a></p><p>این لینک تا ۱۵ دقیقه معتبر است.</p>";
    $plain = "برای بازیابی رمز عبور خود به این آدرس بروید: $reset_link\nاین لینک تا ۱۵ دقیقه معتبر است.";
    return send_email($to, $subject, $html, $plain);
} 