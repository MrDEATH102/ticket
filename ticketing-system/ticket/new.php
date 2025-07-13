<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $priority = $_POST['priority'] ?? 'low';
    $user_id = $_SESSION['user_id'];
    $attachment = null;
    // اعتبارسنجی
    if (!$title || !$message) {
        $errors[] = 'عنوان و پیام الزامی است.';
    }
    // آپلود فایل
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg','image/png','application/pdf'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $fileType = mime_content_type($_FILES['attachment']['tmp_name']);
        if (!in_array($fileType, $allowed)) {
            $errors[] = 'فقط فایل jpg, png, pdf مجاز است.';
        } elseif ($_FILES['attachment']['size'] > $maxSize) {
            $errors[] = 'حجم فایل نباید بیش از ۲ مگابایت باشد.';
        } else {
            $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('att_') . '.' . $ext;
            $dest = __DIR__ . '/../uploads/' . $filename;
            // ساخت پوشه uploads اگر وجود نداشت
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                $attachment = $filename;
            } else {
                $errors[] = 'آپلود فایل انجام نشد.';
            }
        }
    }
    // ثبت تیکت
    if (!$errors) {
        // تولید کد یکتا
        $stmt = $pdo->query('SELECT MAX(id) as maxid FROM tickets');
        $maxid = ($stmt->fetch()['maxid'] ?? 0) + 1;
        $unique_code = 'TKT-' . str_pad($maxid, 6, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('INSERT INTO tickets (user_id, title, priority, status, unique_code, attachment) VALUES (?, ?, ?, "open", ?, ?)');
        $stmt->execute([$user_id, $title, $priority, $unique_code, $attachment]);
        $ticket_id = $pdo->lastInsertId();
        // پیام اولیه
        $stmt = $pdo->prepare('INSERT INTO ticket_messages (ticket_id, sender_id, message, attachment) VALUES (?, ?, ?, ?)');
        $stmt->execute([$ticket_id, $user_id, $message, $attachment]);
        header('Location: ../dashboard/user.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
    <h2>ارسال تیکت جدید</h2>
    <a href="../dashboard/user.php">بازگشت به داشبورد</a>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input name="title" placeholder="عنوان" required><br>
        <textarea name="message" placeholder="پیام" required></textarea><br>
        <select name="priority">
            <option value="low">کم</option>
            <option value="medium">متوسط</option>
            <option value="high">زیاد</option>
        </select><br>
        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"><br>
        <button type="submit">ارسال تیکت</button>
    </form>
</body>
</html> 