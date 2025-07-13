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
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, mobile, password, center_name, center_address, role) VALUES (?, ?, ?, ?, ?, ?, ?, "user")');
            $stmt->execute([$first_name, $last_name, $email, $mobile, $hash, $center_name, $center_address]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['role'] = 'user';
            header('Location: dashboard/user.php');
            exit;
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
    <h2>فرم ثبت‌نام</h2>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post">
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