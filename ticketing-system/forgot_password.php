<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/mailer.php';
session_start();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        $errors[] = 'ایمیل خود را وارد کنید.';
    } else {
        $stmt = $pdo->prepare('SELECT id, reset_token_expiry FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user) {
            $errors[] = 'اگر ایمیل شما ثبت شده باشد، لینک بازیابی ارسال خواهد شد.';
        } else {
            // Cooldown: 2 minutes
            if ($user['reset_token_expiry'] && strtotime($user['reset_token_expiry']) > time() && strtotime($user['reset_token_expiry']) - time() > 13*60) {
                $errors[] = 'درخواست قبلی شما هنوز فعال است. لطفاً کمی بعد دوباره تلاش کنید.';
            } else {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', time() + 15 * 60); // 15 min expiry
                $stmt = $pdo->prepare('UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?');
                $stmt->execute([$token, $expiry, $user['id']]);
                $reset_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=$token";
                send_password_reset_email($email, $reset_link);
                $success = 'اگر ایمیل شما ثبت شده باشد، لینک بازیابی ارسال خواهد شد.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>فراموشی رمز عبور</title>
</head>
<body>
    <h2>فراموشی رمز عبور</h2>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <?php if ($success): ?>
        <p style="color:green;"><?= $success ?></p>
    <?php endif; ?>
    <form method="post">
        <input name="email" type="email" placeholder="ایمیل" required><br>
        <button type="submit">ارسال لینک بازیابی</button>
    </form>
    <p><a href="login.php">بازگشت به ورود</a></p>
</body>
</html> 