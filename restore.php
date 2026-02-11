<?php
session_start();
include 'db.php';
include 'auth.php';

checkLogin();
if (!isAdmin()) {
    header('Location: e-Book.php');
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: hidden_items.php?error=ไม่มี ID ที่ต้องการกู้คืน');
    exit();
}

// กู้คืนข้อมูลโดยตั้ง hidden = 0
$stmt = $conn->prepare("UPDATE items SET hidden = 0 WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    header('Location: hidden_items.php?success=กู้คืนข้อมูลสำเร็จ');
} else {
    header('Location: hidden_items.php?error=กู้คืนข้อมูลล้มเหลว');
}
exit();
?>
