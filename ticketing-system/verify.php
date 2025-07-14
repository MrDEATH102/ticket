<?php
session_start();
$errors = [];
$is_signup = isset($_GET['signup']) && isset($_SESSION['signup_2fa_code'], $_SESSION['signup_2fa_expire'], $_SESSION['signup_data']);
$is_login = isset($_SESSION['2fa_code'], $_SESSION['2fa_expire'], $_SESSION['2fa_user_id'], $_SESSION['2fa_role']);

if (!$is_signup && !$is_login) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_code = trim($_POST['code'] ?? '');
    if (!$input_code) {
        $errors[] = 'کد را وارد کنید.';
    } elseif ($is_signup && time() > $_SESSION['signup_2fa_expire']) {
        $errors[] = 'کد منقضی شده است. لطفاً دوباره ثبت‌نام کنید.';
        unset($_SESSION['signup_2fa_code'], $_SESSION['signup_2fa_expire'], $_SESSION['signup_data']);
    } elseif ($is_signup && $input_code != $_SESSION['signup_2fa_code']) {
        $errors[] = 'کد وارد شده صحیح نیست.';
    } elseif ($is_signup) {
        // Create user in DB after successful verification
        require_once __DIR__ . '/config.php';
        $data = $_SESSION['signup_data'];
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, mobile, password, center_name, center_address, role) VALUES (?, ?, ?, ?, ?, ?, ?, "user")');
        $stmt->execute([
            $data['first_name'], $data['last_name'], $data['email'], $data['mobile'], $hash, $data['center_name'], $data['center_address']
        ]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['role'] = 'user';
        $_SESSION['nav_token'] = bin2hex(random_bytes(16));
        unset($_SESSION['signup_2fa_code'], $_SESSION['signup_2fa_expire'], $_SESSION['signup_data']);
        header('Location: dashboard/user.php');
        exit;
    } elseif ($is_login && time() > $_SESSION['2fa_expire']) {
        $errors[] = 'کد منقضی شده است. لطفاً دوباره وارد شوید.';
        session_unset();
        session_destroy();
    } elseif ($is_login && $input_code != $_SESSION['2fa_code']) {
        $errors[] = 'کد وارد شده صحیح نیست.';
    } elseif ($is_login) {
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
    <h2><?= $is_signup ? 'تایید ایمیل ثبت‌نام' : 'تایید دو مرحله‌ای' ?></h2>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post" autocomplete="none">
        <input name="code" placeholder="کد ۶ رقمی ارسال شده به ایمیل" required><br>
        <button type="submit">تایید</button>
        <button id="resendBtn" disabled type="button" onclick="location.reload()">ارسال مجدد کد</button>
        <?php if ($is_signup): ?>
            <button type="button" onclick="window.location.href='register.php?edit=1'">بازگشت به ثبت‌نام</button>
        <?php endif; ?>
    </form>
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
    </script>
    <p>کد به ایمیل شما ارسال شد: 
        <?= $is_signup ? htmlspecialchars($_SESSION['signup_data']['email']) : (isset($_SESSION['2fa_email']) ? htmlspecialchars($_SESSION['2fa_email']) : '') ?>
    </p>
</body>

</html>