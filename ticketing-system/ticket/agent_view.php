<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header('Location: ../login.php');
    exit;
}
$agent_id = $_SESSION['user_id'];
$ticket_id = intval($_GET['id'] ?? 0);
// دریافت تیکت فقط اگر آزاد باشد یا به این agent اختصاص یافته باشد
$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ? AND (agent_id IS NULL OR agent_id = ?)');
$stmt->execute([$ticket_id, $agent_id]);
$ticket = $stmt->fetch();
if (!$ticket) {
    echo 'تیکت یافت نشد یا به شما اختصاص ندارد.';
    exit;
}
// اختصاص تیکت آزاد به agent
if ($ticket['agent_id'] === null) {
    $pdo->prepare('UPDATE tickets SET agent_id = ? WHERE id = ?')->execute([$agent_id, $ticket_id]);
    $ticket['agent_id'] = $agent_id;
}
// دریافت پیام‌ها
$stmt = $pdo->prepare('SELECT m.*, u.first_name, u.last_name, u.role FROM ticket_messages m JOIN users u ON m.sender_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC');
$stmt->execute([$ticket_id]);
$messages = $stmt->fetchAll();
$errors = [];
// ارسال پاسخ یا تغییر وضعیت
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'reply') {
        $message = trim($_POST['message'] ?? '');
        $attachment = null;
        if (!$message) {
            $errors[] = 'متن پیام الزامی است.';
        }
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','application/pdf'];
            $maxSize = 2 * 1024 * 1024;
            $fileType = mime_content_type($_FILES['attachment']['tmp_name']);
            if (!in_array($fileType, $allowed)) {
                $errors[] = 'فقط فایل jpg, png, pdf مجاز است.';
            } elseif ($_FILES['attachment']['size'] > $maxSize) {
                $errors[] = 'حجم فایل نباید بیش از ۲ مگابایت باشد.';
            } else {
                $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('att_') . '.' . $ext;
                $dest = __DIR__ . '/../uploads/' . $filename;
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest)) {
                    $attachment = $filename;
                } else {
                    $errors[] = 'آپلود فایل انجام نشد.';
                }
            }
        }
        if (!$errors) {
            $stmt = $pdo->prepare('INSERT INTO ticket_messages (ticket_id, sender_id, message, attachment) VALUES (?, ?, ?, ?)');
            $stmt->execute([$ticket_id, $agent_id, $message, $attachment]);
            // تغییر وضعیت تیکت به answered
            $pdo->prepare('UPDATE tickets SET status = "answered" WHERE id = ?')->execute([$ticket_id]);
            header('Location: agent_view.php?id=' . $ticket_id);
            exit;
        }
    } elseif ($action === 'status') {
        $new_status = $_POST['new_status'] ?? '';
        $allowed_status = ['open','answered','pending','closed'];
        if (in_array($new_status, $allowed_status)) {
            $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?')->execute([$new_status, $ticket_id]);
            header('Location: agent_view.php?id=' . $ticket_id);
            exit;
        }
    }
}
function status_fa($status) {
    switch ($status) {
        case 'open': return 'باز';
        case 'answered': return 'پاسخ داده شده';
        case 'pending': return 'در انتظار';
        case 'closed': return 'بسته';
        default: return $status;
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <?php include __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
    <a href="../dashboard/agent.php">بازگشت به داشبورد</a>
    <h2>تیکت: <?= htmlspecialchars($ticket['unique_code']) ?> - <?= htmlspecialchars($ticket['title']) ?></h2>
    <p>وضعیت: <?= status_fa($ticket['status']) ?> | اولویت: <?= htmlspecialchars($ticket['priority']) ?></p>
    <hr>
    <?php foreach ($messages as $msg): ?>
        <div style="margin-bottom:15px; border-bottom:1px solid #ccc;">
            <b><?= htmlspecialchars($msg['first_name'] . ' ' . $msg['last_name']) ?> (<?= $msg['role'] === 'agent' ? 'پشتیبان' : 'کاربر' ?>):</b>
            <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
            <?php if ($msg['attachment']): ?>
                <div><a href="../uploads/<?= htmlspecialchars($msg['attachment']) ?>" target="_blank">دانلود فایل ضمیمه</a></div>
            <?php endif; ?>
            <small><?= htmlspecialchars($msg['created_at']) ?></small>
        </div>
    <?php endforeach; ?>
    <h3>ارسال پاسخ</h3>
    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <textarea name="message" placeholder="متن پیام" required></textarea><br>
        <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf"><br>
        <button type="submit" name="action" value="reply">ارسال پاسخ</button>
    </form>
    <h3>تغییر وضعیت تیکت</h3>
    <form method="post">
        <select name="new_status">
            <option value="open" <?= $ticket['status']==='open'?'selected':'' ?>>باز</option>
            <option value="answered" <?= $ticket['status']==='answered'?'selected':'' ?>>پاسخ داده شده</option>
            <option value="pending" <?= $ticket['status']==='pending'?'selected':'' ?>>در انتظار</option>
            <option value="closed" <?= $ticket['status']==='closed'?'selected':'' ?>>بسته</option>
        </select>
        <button type="submit" name="action" value="status">تغییر وضعیت</button>
    </form>
</body>
</html> 