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
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'admin') {
                header('Location: admin/index.php');
            } elseif ($user['role'] === 'agent') {
                header('Location: dashboard/agent.php');
            } else {
                header('Location: dashboard/user.php');
            }
            exit;
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
    </div>
</body>

</html>