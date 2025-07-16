<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/config.php';

$is_signup = isset($_GET['signup']) && $_GET['signup'] == '1';
$now = time();

if ($is_signup) {
    if (!isset($_SESSION['signup_data'])) {
        echo json_encode(['success' => false, 'message' => 'داده ثبت‌نام یافت نشد.']);
        exit;
    }
    // Only allow resend if expired
    if (isset($_SESSION['signup_2fa_expire']) && $now < $_SESSION['signup_2fa_expire']) {
        echo json_encode(['success' => false, 'message' => 'هنوز امکان ارسال مجدد وجود ندارد.']);
        exit;
    }
    $code = rand(100000, 999999);
    $expire = $now + 120;
    $_SESSION['signup_2fa_code'] = $code;
    $_SESSION['signup_2fa_expire'] = $expire;
    $email = $_SESSION['signup_data']['email'];
    send_email($email, 'کد تایید ثبت‌نام', "<p>کد تایید شما: <b>$code</b></p>", "کد تایید شما: $code");
    echo json_encode(['success' => true, 'expireAt' => $expire, 'now' => $now]);
    exit;
} else {
    // Login 2FA
    if (!isset($_SESSION['2fa_email'], $_SESSION['2fa_user_id'])) {
        echo json_encode(['success' => false, 'message' => 'داده ورود یافت نشد.']);
        exit;
    }
    if (isset($_SESSION['2fa_expire']) && $now < $_SESSION['2fa_expire']) {
        echo json_encode(['success' => false, 'message' => 'هنوز امکان ارسال مجدد وجود ندارد.']);
        exit;
    }
    $code = rand(100000, 999999);
    $expire = $now + 120;
    $_SESSION['2fa_code'] = $code;
    $_SESSION['2fa_expire'] = $expire;
    $email = $_SESSION['2fa_email'];
    send_email($email, 'کد تایید ورود', "<p>کد تایید شما: <b>$code</b></p>", "کد تایید شما: $code");
    echo json_encode(['success' => true, 'expireAt' => $expire, 'now' => $now]);
    exit;
} 