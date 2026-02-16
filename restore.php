<?php
function safe_redirect(string $url): void
{
    if (!headers_sent()) {
        header('Location: ' . $url);
    } else {
        echo '<script>window.location.href=' . json_encode($url) . ';</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
    }
    exit();
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (!headers_sent()) {
        session_start();
    } else {
        safe_redirect('login.php');
    }
}
include 'db.php';
include 'auth.php';

checkLogin();
if (!isAdmin()) {
    safe_redirect('e-Book.php');
}

$id = $_GET['id'] ?? null;
if (!$id) {
    safe_redirect('hidden_items.php?error=ไม่มี ID ที่ต้องการกู้คืน');
}

// กู้คืนข้อมูลโดยตั้ง hidden = 0
$stmt = $conn->prepare("UPDATE items SET hidden = 0 WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    safe_redirect('hidden_items.php?success=กู้คืนข้อมูลสำเร็จ');
} else {
    safe_redirect('hidden_items.php?error=กู้คืนข้อมูลล้มเหลว');
}
?>
