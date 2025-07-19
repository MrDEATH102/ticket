<?php
require_once __DIR__ . '/config.php';
session_start();

$errors = [];
$success = '';
$show_form = true;

$token = $_GET['token'] ?? '';
if (!$token) {
    $errors[] = 'درخواست نامعتبر است.';
    $show_form = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (!$password || !$password2) {
        $errors[] = 'رمز عبور جدید را وارد کنید.';
    } elseif ($password !== $password2) {
        $errors[] = 'رمز عبور و تکرار آن یکسان نیست.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    } else {
        $stmt = $pdo->prepare('SELECT id, reset_token_expiry FROM users WHERE reset_token = ?');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if (!$user || !$user['reset_token_expiry'] || strtotime($user['reset_token_expiry']) < time()) {
            $errors[] = 'توکن بازیابی نامعتبر یا منقضی شده است.';
            $show_form = false;
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?');
            $stmt->execute([$hash, $user['id']]);
            $success = 'رمز عبور با موفقیت تغییر کرد. اکنون می‌توانید وارد شوید.';
            $show_form = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/includes/head.php'; ?>
    <title>بازیابی رمز عبور</title>
</head>
<body>
    <h2>بازیابی رمز عبور</h2>
    <?php if ($errors): ?>
        <ul class="error-messages">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success-messages"><?= $success ?></p>
        <p><a href="login.php">ورود</a></p>
    <?php endif; ?>
    <?php if ($show_form): ?>
    <form method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <input name="password" type="password" placeholder="رمز عبور جدید" required><br>
        <input name="password2" type="password" placeholder="تکرار رمز عبور جدید" required><br>
        <button type="submit">تغییر رمز عبور</button>
    </form>
    <?php endif; ?>
</body>
</html> 