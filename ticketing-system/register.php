<?php
require_once __DIR__ . '/config.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $center_name = trim($_POST['center_name'] ?? '');
    $center_address = trim($_POST['center_address'] ?? '');

    // اعتبارسنجی ساده
    if (!$first_name || !$last_name || !$email || !$mobile || !$password) {
        $errors[] = 'لطفاً تمام فیلدهای ضروری را پر کنید.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ایمیل معتبر نیست.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    }

    // اگر خطا نبود، ثبت در دیتابیس
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR mobile = ?');
        $stmt->execute([$email, $mobile]);
        if ($stmt->fetch()) {
            $errors[] = 'ایمیل یا موبایل قبلاً ثبت شده است.';
        } else {
            // Generate 2FA code and expiration
            $code = rand(100000, 999999);
            $expire = time() + 120; // 2 minutes
            // Store signup data and 2FA in session
            $_SESSION['signup_data'] = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'mobile' => $mobile,
                'password' => $password, // Store plain for now, hash after verification
                'center_name' => $center_name,
                'center_address' => $center_address
            ];
            $_SESSION['signup_2fa_code'] = $code;
            $_SESSION['signup_2fa_expire'] = $expire;
            // Send code to email
            require_once __DIR__ . '/includes/mailer.php';
            send_email($email, 'کد تایید ثبت‌نام', "<p>کد تایید شما: <b>$code</b></p>", "کد تایید شما: $code");
            // Redirect to verification page
            header('Location: verify.php?signup=1');
            exit;
        }
    }
}

if (isset($_GET['edit'])) {
    unset($_SESSION['signup_2fa_code'], $_SESSION['signup_2fa_expire'], $_SESSION['signup_data']);
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>
    <h2>فرم ثبت‌نام</h2>
    <?php if ($errors): ?>
        <ul class="error-messages">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <input name="first_name" placeholder="نام" required><br>
        <input name="last_name" placeholder="نام خانوادگی" required><br>
        <input name="email" type="email" placeholder="ایمیل" required><br>
        <input name="mobile" placeholder="موبایل" required><br>
        <input name="center_name" placeholder="نام مرکز / مطب"><br>
        <input name="center_address" placeholder="آدرس مرکز"><br>
        <input name="password" type="password" placeholder="رمز عبور" required><br>
        <button type="submit">ثبت‌نام</button>
    </form>
    <p>حساب دارید؟ <a href="login.php">ورود</a></p>
</body>
</html> 