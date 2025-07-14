<?php
session_start();
$errors = [];
if (!isset($_SESSION['2fa_code'], $_SESSION['2fa_expire'], $_SESSION['2fa_user_id'], $_SESSION['2fa_role'])) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_code = trim($_POST['code'] ?? '');
    if (!$input_code) {
        $errors[] = 'کد را وارد کنید.';
    } elseif (time() > $_SESSION['2fa_expire']) {
        $errors[] = 'کد منقضی شده است. لطفاً دوباره وارد شوید.';
        session_unset();
        session_destroy();
    } elseif ($input_code != $_SESSION['2fa_code']) {
        $errors[] = 'کد وارد شده صحیح نیست.';
    } else {
        // ورود نهایی
        $_SESSION['user_id'] = $_SESSION['2fa_user_id'];
        $_SESSION['role'] = $_SESSION['2fa_role'];
        $_SESSION['nav_token'] = bin2hex(random_bytes(16));
        unset($_SESSION['2fa_code'], $_SESSION['2fa_expire'], $_SESSION['2fa_user_id'], $_SESSION['2fa_role'], $_SESSION['2fa_email']);
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin/index.php');
        } elseif ($_SESSION['role'] === 'agent') {
            header('Location: dashboard/agent.php');
        } else {
            header('Location: dashboard/user.php');
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fa">

<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>

<body>
    <h2>تایید دو مرحله‌ای</h2>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post">
        <input name="code" placeholder="کد ۶ رقمی ارسال شده به ایمیل" required><br>
        <button type="submit">تایید</button>
    </form>
    <!-- new code  -->
    <button id="resendBtn" disabled>ارسال مجدد کد</button>
    <p id="timer"></p>
    <script>
        let cooldown = 120;
        let timerDisplay = document.getElementById('timer');
        let resendBtn = document.getElementById('resendBtn');

        function updateTimer() {
            if (cooldown > 0) {
                resendBtn.disabled = true;
                timerDisplay.textContent = `امکان ارسال مجدد کد تا ${cooldown} ثانیه دیگر.`;
                cooldown--;
            } else {
                resendBtn.disabled = false;
                timerDisplay.textContent = '';
            }
        }
        updateTimer();
        let interval = setInterval(updateTimer, 1000);
        resendBtn.addEventListener('click', function() {
            location.reload(); // Reload to trigger backend resend logic
        });
    </script>
    <p>کد به ایمیل شما ارسال شد: <?= htmlspecialchars($_SESSION['2fa_email']) ?></p>
</body>

</html>