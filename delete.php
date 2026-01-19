<?php
session_start();
include 'db.php';
include 'auth.php';

checkLogin();

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: e-Book.php?error=ไม่มี ID ที่ต้องการลบ');
    exit();
}

// ดึงข้อมูลเพื่อตรวจสอบก่อนซ่อน
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    header('Location: e-Book.php?error=ไม่พบข้อมูล');
    exit();
}

if (isUser()) {
    // ตรวจสอบว่ามี items_backup หรือไม่
    $conn->query("CREATE TABLE IF NOT EXISTS items_backup LIKE items");

    // **ตรวจสอบและเปลี่ยน ID ใหม่เพื่อป้องกันการซ้ำ**
    $stmt = $conn->prepare("INSERT INTO items_backup (name, details, image, pdf_file, created_at, hidden)
                            SELECT name, details, image, pdf_file, created_at, 1 FROM items WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        // อัปเดตเฉพาะแถวที่มี ID ตรงกันให้ซ่อน
        $stmt = $conn->prepare("UPDATE items SET hidden = 1 WHERE id = ?");
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            header('Location: e-Book.php?success=ซ่อนข้อมูลสำเร็จ');
        } else {
            header('Location: e-Book.php?error=ไม่สามารถซ่อนข้อมูลได้');
        }
    } else {
        header('Location: e-Book.php?error=ไม่สามารถย้ายข้อมูลไปยังฐานข้อมูลสำรองได้');
    }
    exit();
}

if (isAdmin()) {
    // Admin สามารถลบจริงได้
    if (!empty($item['image']) && file_exists(__DIR__ . '/uploads/' . $item['image'])) {
        unlink(__DIR__ . '/uploads/' . $item['image']);
    }

    if (!empty($item['pdf_file']) && file_exists(__DIR__ . '/uploads/' . $item['pdf_file'])) {
        unlink(__DIR__ . '/uploads/' . $item['pdf_file']);
    }

    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        header('Location: e-Book.php?success=ลบข้อมูลสำเร็จ');
    } else {
        header('Location: e-Book.php?error=การลบข้อมูลล้มเหลว');
    }
    exit();
}

?>
