<?php
require_once __DIR__ . '/includes/src/PHPMailer.php';
require_once __DIR__ . '/includes/src/SMTP.php';
require_once __DIR__ . '/includes/src/Exception.php';
require_once __DIR__ . '/includes/config_mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $mail->addAddress('massamongus@gmail.com'); // <-- put your real email here

    $mail->isHTML(true);
    $mail->Subject = 'Test Email from XAMPP';
    $mail->Body    = '<b>This is a test email sent from PHPMailer on XAMPP.</b>';
    $mail->AltBody = 'This is a test email sent from PHPMailer on XAMPP.';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}