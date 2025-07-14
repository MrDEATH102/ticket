<?php
require_once __DIR__ . '/config.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'ایمیل و رمز عبور الزامی است.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            // مرحله دوم: ارسال کد تایید دو مرحله‌ای
            require_once __DIR__ . '/includes/mailer.php';
            // Cooldown: 2 minutes
            if (isset($user['reset_token_expiry']) && $user['reset_token_expiry'] && strtotime($user['reset_token_expiry']) > time() && strtotime($user['reset_token_expiry']) - time() > 13*60) {
                $errors[] = 'درخواست ورود قبلی شما هنوز فعال است. لطفاً کمی بعد دوباره تلاش کنید.';
            } else {
                $code = rand(100000, 999999);
                $_SESSION['2fa_code'] = $code;
                $_SESSION['2fa_expire'] = time() + 300; // 5 دقیقه
                $_SESSION['2fa_user_id'] = $user['id'];
                $_SESSION['2fa_role'] = $user['role'];
                $_SESSION['2fa_email'] = $user['email'];
                $html = "<p>کد ورود دومرحله‌ای شما:</p><h2>$code</h2>";
                $plain = "کد ورود شما: $code";
                send_email($user['email'], "کد ورود دومرحله‌ای شما – elaico", $html, $plain);
                // Set/reset cooldown (reuse reset_token_expiry for simplicity)
                $expiry = date('Y-m-d H:i:s', time() + 120); // 2 min
                $stmt = $pdo->prepare('UPDATE users SET reset_token_expiry = ? WHERE id = ?');
                $stmt->execute([$expiry, $user['id']]);
                header('Location: verify.php');
                exit;
            }
        } else {
            $errors[] = 'ایمیل یا رمز عبور اشتباه است.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body>
    <h2>ورود</h2>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post">
        <input name="email" type="email" placeholder="ایمیل" required><br>
        <input name="password" type="password" placeholder="رمز عبور" required><br>
        <button type="submit">ورود</button>
    </form>
    <div class="sign-para">
        <p>حساب ندارید؟ <a href="register.php">ثبت‌نام</a></p>
        <p><a href="forgot_password.php">رمز عبور را فراموش کرده‌اید؟</a></p>
    </div>
</body>

</html>