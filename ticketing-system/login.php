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
            if (isset($user['reset_token_expiry']) && $user['reset_token_expiry'] && strtotime($user['reset_token_expiry']) > time() && strtotime($user['reset_token_expiry']) - time() > 13 * 60) {
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
    <form method="post">
        <input name="email" type="email" placeholder="ایمیل" required><br>
        <!-- eye icon -->
        <div style="position:relative; display:inline-block; width: 100%;">
            <input id="password" name="password" type="password" placeholder="رمز عبور" required>
            <span id="togglePassword" style="position:absolute; left:8px; top:50%; transform:translateY(-50%); cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="gray">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </span>
        </div><br>
        <button type="submit">ورود</button>
    </form>
    <div class="sign-para">
        <p>حساب ندارید؟ <a href="register.php">ثبت‌نام</a></p>
        <p><a href="forgot_password.php">رمز عبور را فراموش کرده‌اید؟</a></p>
    </div>
    <?php if ($errors): ?>
        <ul style="color:red; display:flex; align-items:center; justify-content:center;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <!-- script  -->
    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });
    </script>
</body>

</html>