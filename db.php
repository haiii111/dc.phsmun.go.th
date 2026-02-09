<?php
date_default_timezone_set('Asia/Bangkok');
// $host = 'mariadb_dctest';
// $user = 'admin_dctest';
// $password = 'DataCenter@2025';
// $dbname = 'dctest';
$host = 'localhost';
$host = 'localhost';
$user = 'root';
$password = ''; // ใส่รหัสผ่านฐานข้อมูลของคุณ
$dbname = 'dctest';


// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($host, $user, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// บังคับให้การเชื่อมต่อใช้ UTF-8 เพื่อให้ชื่อไฟล์ภาษาไทยแสดงถูกต้อง
if (!$conn->set_charset('utf8mb4')) {
    die("ตั้งค่า charset ล้มเหลว: " . $conn->error);
}
