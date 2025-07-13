<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$first_name || !$last_name || !$email || !$mobile || !$password) {
        $errors[] = 'همه فیلدها الزامی است.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ایمیل معتبر نیست.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    }
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR mobile = ?');
        $stmt->execute([$email, $mobile]);
        if ($stmt->fetch()) {
            $errors[] = 'ایمیل یا موبایل قبلاً ثبت شده است.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (first_name, last_name, email, mobile, password, role) VALUES (?, ?, ?, ?, ?, "agent")');
            $stmt->execute([$first_name, $last_name, $email, $mobile, $hash]);
            header('Location: users.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
    <a href="index.php">بازگشت به پنل مدیریت</a>
    <h2>افزودن پشتیبان جدید</h2>
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
        <input name="password" type="password" placeholder="رمز عبور" required><br>
        <button type="submit">افزودن پشتیبان</button>
    </form>
</body>
</html> 