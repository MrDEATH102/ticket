<?php
require_once __DIR__ . '/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$step = isset($_SESSION['change_pass_2fa_code']) ? 2 : 1;
$errors = [];
$success = '';
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        if (!$new_password || !$confirm_password) {
            $errors[] = 'رمز عبور جدید و تکرار آن الزامی است.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'رمز عبور و تکرار آن یکسان نیست.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
        }
        if (!$errors) {
            $code = rand(100000, 999999);
            $_SESSION['change_pass_new'] = $new_password;
            $_SESSION['change_pass_2fa_code'] = $code;
            $_SESSION['change_pass_2fa_expire'] = time() + 120;
            require_once __DIR__ . '/includes/mailer.php';
            send_email($email, 'کد تایید تغییر رمز عبور', "<p>کد تایید شما: <b>$code</b></p>", "کد تایید شما: $code");
            header('Location: change_password.php');
            exit;
        }
    } elseif ($step === 2) {
        $input_code = trim($_POST['code'] ?? '');
        if (!$input_code) {
            $errors[] = 'کد را وارد کنید.';
        } elseif (time() > $_SESSION['change_pass_2fa_expire']) {
            $errors[] = 'کد منقضی شده است. لطفاً دوباره تلاش کنید.';
            unset($_SESSION['change_pass_2fa_code'], $_SESSION['change_pass_2fa_expire'], $_SESSION['change_pass_new']);
            $step = 1;
        } elseif ($input_code != $_SESSION['change_pass_2fa_code']) {
            $errors[] = 'کد وارد شده صحیح نیست.';
        } else {
            $hash = password_hash($_SESSION['change_pass_new'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hash, $user_id]);
            unset($_SESSION['change_pass_2fa_code'], $_SESSION['change_pass_2fa_expire'], $_SESSION['change_pass_new']);
            $success = 'رمز عبور با موفقیت تغییر کرد.';
        }
    }
}

// Theme toggle script (same as other pages)
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>تغییر رمز عبور</title>
</head>
<body>
<div class="toggle-theme" onclick="toggleTheme()">🌓</div>
<script>
function toggleTheme() {
    const root = document.documentElement;
    const theme = root.getAttribute('data-theme') === 'dark' ? '' : 'dark';
    root.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
}
(function() {
    const theme = localStorage.getItem('theme');
    if (theme) document.documentElement.setAttribute('data-theme', theme);
})();
</script>
<div style="max-width:400px;margin:40px auto;">
    <h2>تغییر رمز عبور</h2>
    <p>برای تغییر رمز عبور، ابتدا رمز جدید را وارد کنید. سپس یک کد تایید به ایمیل شما ارسال می‌شود و باید آن را وارد کنید.</p>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:green;">رمز عبور با موفقیت تغییر کرد.</div>
        <a href="dashboard/user.php">بازگشت به داشبورد</a>
    <?php elseif ($step === 1): ?>
        <form method="post" autocomplete="off">
            <input name="new_password" type="password" placeholder="رمز عبور جدید" required><br>
            <input name="confirm_password" type="password" placeholder="تکرار رمز عبور جدید" required><br>
            <button type="submit">ارسال کد تایید</button>
        </form>
    <?php elseif ($step === 2): ?>
        <form method="post" autocomplete="off">
            <input name="code" placeholder="کد تایید ارسال شده به ایمیل" required><br>
            <button type="submit">تایید و تغییر رمز عبور</button>
        </form>
        <p style="color:#888;">کد به ایمیل شما ارسال شد: <?= htmlspecialchars($email) ?></p>
    <?php endif; ?>
    <p><a href="dashboard/user.php">بازگشت به داشبورد</a></p>
</div>
</body>
</html> 