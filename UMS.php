<?php
session_start();
include 'db.php';
include 'auth.php';

// สร้าง CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ตรวจสอบสิทธิ์แอดมิน
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// ฟังก์ชันอนุมัติสมาชิก
if (isset($_POST['approve_user']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $id = (int)$_POST['user_id'];
    $conn->begin_transaction();
    try {
        // ดึงข้อมูลจาก pending_users
        $stmt = $conn->prepare("SELECT username, password, role FROM pending_users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // ตรวจสอบว่า username ซ้ำในตาราง users หรือไม่
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_stmt->bind_param("s", $user['username']);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $conn->rollback();
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <i class='fas fa-exclamation-circle me-2'></i>ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
            } else {
                // เพิ่มข้อมูลลงตาราง users
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $user['username'], $user['password'], $user['role']);
                $stmt->execute();

                // ลบข้อมูลจาก pending_users
                $stmt = $conn->prepare("DELETE FROM pending_users WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();

                $conn->commit();
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <i class='fas fa-check-circle me-2'></i>อนุมัติสมาชิกสำเร็จ!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
            }
            $check_stmt->close();
        } else {
            $conn->rollback();
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class='fas fa-exclamation-circle me-2'></i>ไม่พบข้อมูลผู้ใช้
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error approving user: " . $e->getMessage());
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle me-2'></i>เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage()) . "
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    }
}

// ฟังก์ชันปฏิเสธสมาชิก
if (isset($_POST['reject_user']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $id = (int)$_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM pending_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <i class='fas fa-check-circle me-2'></i>ปฏิเสธคำขอสมัครสำเร็จ!
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    } else {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle me-2'></i>เกิดข้อผิดพลาดในการปฏิเสธ!
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    }
    $stmt->close();
}

// ฟังก์ชันเพิ่มสมาชิก
if (isset($_POST['add_member']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? UNION SELECT id FROM pending_users WHERE username = ?");
    $check_stmt->bind_param("ss", $username, $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle me-2'></i>ชื่อผู้ใช้นี้มีอยู่แล้ว กรุณาใช้ชื่ออื่น
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    <i class='fas fa-check-circle me-2'></i>เพิ่มสมาชิกสำเร็จ!
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
        } else {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class='fas fa-exclamation-circle me-2'></i>เกิดข้อผิดพลาด: " . htmlspecialchars($stmt->error) . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// ฟังก์ชันลบสมาชิก
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <i class='fas fa-check-circle me-2'></i>ลบสมาชิกสำเร็จ!
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    } else {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle me-2'></i>เกิดข้อผิดพลาดในการลบ!
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    }
    $stmt->close();
}

// ฟังก์ชันรีเซ็ตรหัสผ่าน
if (isset($_POST['reset_password']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $id = (int)$_POST['user_id'];
    $new_password = trim($_POST['new_password']);

    if (empty($new_password)) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle me-2'></i>กรุณาป้อนรหัสผ่านใหม่
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if (!$stmt) {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class='fas fa-exclamation-circle me-2'></i>Prepare failed: " . htmlspecialchars($conn->error) . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
        } else {
            $stmt->bind_param("si", $hashed_password, $id);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <i class='fas fa-check-circle me-2'></i>รีเซ็ตรหัสผ่านสำเร็จ!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
            } else {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <i class='fas fa-exclamation-circle me-2'></i>เกิดข้อผิดพลาด: " . htmlspecialchars($stmt->error) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
            }
            $stmt->close();
        }
    }
}

// ฟังก์ชันแก้ไขสิทธิ์
if (isset($_POST['edit_role']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $id = (int)$_POST['user_id'];
    $new_role = $_POST['new_role'];

    if (!in_array($new_role, ['user', 'admin'])) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle me-2'></i>สิทธิ์ไม่ถูกต้อง
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>";
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        if (!$stmt) {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    <i class='fas fa-exclamation-circle me-2'></i>Prepare failed: " . htmlspecialchars($conn->error) . "
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
        } else {
            $stmt->bind_param("si", $new_role, $id);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <i class='fas fa-check-circle me-2'></i>แก้ไขสิทธิ์สำเร็จ!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
            } else {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        <i class='fas fa-exclamation-circle me-2'></i>เกิดข้อผิดพลาด: " . htmlspecialchars($stmt->error) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
            }
            $stmt->close();
        }
    }
}

// ดึงข้อมูลสมาชิก
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_result = $conn->query("SELECT COUNT(*) AS total FROM users");
$total_row = $total_result->fetch_assoc();
$total_members = $total_row['total'];

$result = $conn->query("SELECT * FROM users LIMIT $limit OFFSET $offset");
$total_pages = ceil($total_members / $limit);

// ดึงข้อมูลผู้ใช้ที่รอการอนุมัติ
$pending_result = $conn->query("SELECT * FROM pending_users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <style>
        :root {
            color-scheme: light;
            --bg: #ffffff;
            --surface: #ffffff;
            --surface-2: #f7f5ff;
            --primary: #a78bfa;
            --primary-2: #8b5cf6;
            --primary-3: #7c3aed;
            --text: #1f2937;
            --muted: #6b7280;
            --border: rgba(139, 92, 246, 0.25);
            --shadow: 0 10px 24px rgba(139, 92, 246, 0.16);
        }
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            background: var(--bg);
            
            
            color: var(--text);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-size: 1rem;
            display: flex;
            flex-direction: column;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        #particles-js {
            display: none;
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .container-fluid {
            max-width: 95%;
            padding: 15px;
            margin: 15px auto;
            background: var(--surface);
            border-radius: 16px;
            box-shadow: var(--shadow);
        }
        h1 {
            color: var(--primary-3);
            font-weight: 700;
            font-size: 2.2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        h2.h4 {
            color: var(--primary-3);
            font-weight: 600;
            font-size: 1.5rem;
        }
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            color: #28a745;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            position: relative;
            margin-top:63px;
        }
        .alert-danger {
            background: rgba(255, 85, 85, 0.1);
            color: #dc3545;
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            position: relative;
            margin-top:63px;

        }
        .alert-info {
            background: rgba(2, 136, 209, 0.1);
            color: #0288d1;
            border: 1px solid #0288d1;
            border-radius: 8px;
            padding: 15px;
            position: relative;
            margin-top:63px;

        }
        .alert .btn-close {
            background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%231a1a1a'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707A1 1 0 01.293.293z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            color: #1a1a1a;
            opacity: 1;
            font-size: 1.1rem;
            padding: 0.75rem;
            width: 1.5em;
            height: 1.5em;
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }
        .table {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid var(--border);
            width: 100%;
            table-layout: auto;
        }
        .table th, .table td {
            padding: 12px;
            color: var(--text);
            vertical-align: middle;
            text-align: center;
            font-size: 0.95rem;
            border: 1px solid var(--border);
        }
        .table th {
            background: var(--bg);
            color: var(--primary-3);
            font-weight: 600;
        }
        .table tbody tr {
            transition: background 0.3s, transform 0.3s;
            opacity: 0;
            transform: translateY(20px);
        }
        .table tbody tr.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .table tbody tr:hover {
            background: #f5f5ff;
            transform: scale(1.01);
        }
        .btn-primary:hover {
            background: #4b2078;
            box-shadow: 0 0 10px rgba(94, 42, 150, 0.5);
        }
        .btn-success:hover {
            background: #25632a;
            box-shadow: 0 0 10px rgba(46, 125, 50, 0.5);
        }
        .btn-danger:hover {
            background: #a90f1f;
            box-shadow: 0 0 10px rgba(198, 40, 40, 0.5);
        }
        .btn-warning:hover {
            background: #e6a800;
            box-shadow: 0 0 10px rgba(255, 179, 0, 0.5);
        }
        .btn-info:hover {
            background: #015d9b;
            box-shadow: 0 0 10px rgba(2, 136, 209, 0.5);
        }
        .btn-copy:hover {
            background: #5a6268;
            box-shadow: 0 0 10px rgba(108, 117, 125, 0.5);
        }
        .form-control:focus, .form-select:focus {
            border-color: #5e2a96;
            box-shadow: 0 0 8px rgba(94, 42, 150, 0.3);
            background: #fff;
        }
        .form-control::placeholder {
            color: #999;
        }
        .form-label {
            color: #333;
            font-weight: 500;
        }
        .form-text {
            color: #666;
        }
        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .pagination .page-link {
            background: #ffffff;
            border: 2px solid rgba(139, 92, 246, 0.45);
            color: var(--primary-3);
            margin: 0 5px;
            border-radius: 10px;
            transition: all 0.2s;
            font-weight: 600;
            font-size: 0.9rem;
            min-width: 36px;
            text-align: center;
        }
        .pagination .page-link:hover,
        .pagination .page-item.active .page-link {
            background: var(--primary-2);
            color: #fff;
        }
        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            border-right: 1px solid #d1c4e9;
            position: fixed;
            top: 0;
            left: -260px;
            width: 260px;
            max-width: 80vw;
            height: 100%;
            transition: left 0.3s ease-in-out;
            z-index: 1000;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            left: 0;
            width: 40px;
            height: 40px;
            background: #5e2a96;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 0 8px 8px 0;
            z-index: 1001;
            transition: left 0.3s ease-in-out;
        }
        .sidebar.active ~ .sidebar-toggle {
            left: 260px;
        }
        .sidebar-toggle:hover {
            background: #4b2078;
        }
        .sidebar-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 60px;
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-3);
            padding: 10px 12px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: background 0.2s, transform 0.2s;
            font-weight: 500;
        }
        .sidebar-item:hover {
            background: var(--bg);
            color: #5e2a96;
            transform: translateX(5px);
        }
        .sidebar-item i {
            font-size: 1.2rem;
            color: #5e2a96;
        }
        @media (max-width: 992px) {
            .container-fluid {
                padding: 12px;
                margin: 10px;
            }
            .input-group {
                flex-direction: column;
                align-items: stretch;
            }
            .form-control, .form-select, .btn {
                width: 100%;
                max-width: 100%;
            }
            .sidebar {
                width: 220px;
                left: -220px;
            }
            .sidebar.active ~ .sidebar-toggle {
                left: 220px;
            }
            h1 {
                font-size: 2rem;
            }
            .table th, .table td {
                font-size: 0.9rem;
                padding: 10px;
            }
            .alert .btn-close {
                font-size: 1rem;
                padding: 0.5rem;
                width: 1.2em;
                height: 1.2em;
            }
        }
        @media (max-width: 576px) {
            body {
                font-size: 0.9rem;
            }
            .container-fluid {
                padding: 10px;
                margin: 8px;
            }
            .table th, .table td {
                padding: 8px;
                font-size: 0.8rem;
            }
            .sidebar-toggle {
                width: 36px;
                height: 36px;
            }
            .alert .btn-close {
                font-size: 0.9rem;
                padding: 0.4rem;
                width: 1em;
                height: 1em;
                top: 8px;
                right: 8px;
            }
        }
        @media (orientation: landscape) and (max-height: 500px) {
            .container-fluid {
                padding: 10px;
            }
            .input-group {
                flex-direction: row;
                flex-wrap: nowrap;
            }
            .alert .btn-close {
                font-size: 1rem;
                padding: 0.5rem;
            }
        }
        /* e-Book theme alignment overrides */
        body { background: var(--bg) !important; color: var(--text) !important; line-height: 1.7; }
        #particles-js { display: none !important; }
        .container-fluid { background: var(--surface) !important; border: 1px solid var(--border) !important; box-shadow: var(--shadow) !important; border-radius: 16px !important; }
        .table th { background: linear-gradient(135deg, #ede9fe, #e9d5ff) !important; color: #4c1d95 !important; font-weight: 700 !important; }
        .btn {
            border-radius: 12px !important;
            box-shadow: 0 6px 14px rgba(139, 92, 246, 0.18) !important;
            border: 1px solid transparent !important;
            transition: transform 0.15s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 22px rgba(139, 92, 246, 0.22) !important;
        }
        .btn:active {
            transform: translateY(0);
            box-shadow: 0 6px 14px rgba(139, 92, 246, 0.18) !important;
        }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-2)) !important; color: #ffffff !important; }
        .btn-primary:hover { box-shadow: 0 10px 18px rgba(139, 92, 246, 0.28) !important; transform: translateY(-1px); }
        .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a) !important; color: #ffffff !important; }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626) !important; color: #ffffff !important; }
        .btn-warning { background: linear-gradient(135deg, #fbbf24, #f59e0b) !important; color: #1f2937 !important; }
        .btn-info { background: linear-gradient(135deg, #38bdf8, #0ea5e9) !important; color: #ffffff !important; }
        .btn-copy { background: linear-gradient(135deg, #94a3b8, #64748b) !important; color: #ffffff !important; }
        .btn-sm { height: 32px; font-size: 0.82rem; padding: 4px 10px; }
        .btn .fas, .btn .bi { font-size: 0.95em; }
        .form-control, .form-select { border: 1px solid var(--border) !important; border-radius: 10px !important; box-shadow: inset 0 1px 2px rgba(139, 92, 246, 0.08) !important; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-2) !important; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2) !important; }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="sidebar">
        <div class="sidebar-content">
            <a href="e-Book.php" class="sidebar-item"><i class="fas fa-home"></i> หน้าหลัก</a>
            <a href="UMS.php" class="sidebar-item"><i class="fas fa-users"></i> ระบบจัดการสมาชิก</a>
            <a href="stats.php" class="sidebar-item"><i class="fas fa-chart-line"></i> สถิติผู้เยี่ยมชมเว็บไซต์</a>
            <a href="hidden_items.php" class="sidebar-item"><i class="bi bi-archive"></i> กู้คืนข้อมูล</a>
            <a href="logout.php" class="sidebar-item"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="sidebar-toggle"><i class="fas fa-bars"></i></div>

    <div class="container-fluid mt-5">
        <h1><i class="fas fa-users me-2"></i>ระบบจัดการสมาชิก</h1>
        <p class="text-center">จำนวนสมาชิกทั้งหมด: <strong><?= $total_members ?></strong> คน</p>
        <!-- เพิ่มปุ่มคัดลอกลิงก์ -->
        <div class="text-center mb-4">
            <button id="copyLinkBtn" class="btn btn-copy" data-link="https://dc.phsmun.go.th/register.php">
                <i class="fas fa-copy me-2"></i>คัดลอกลิงก์สมัครสมาชิก
            </button>
        </div>

        <!-- ตารางผู้ใช้ที่รอการอนุมัติ -->
        <h2 class="h4 mb-3"><i class="fas fa-hourglass-start me-2"></i>คำขอสมัครสมาชิกที่รอการอนุมัติ</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>วันที่สมัคร</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pending_result->num_rows > 0): ?>
                        <?php while ($row = $pending_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <button type="submit" name="approve_user" class="btn btn-sm btn-success"><i class="fas fa-check me-2"></i>อนุมัติ</button>
                                        <button type="submit" name="reject_user" class="btn btn-sm btn-danger"><i class="fas fa-times me-2"></i>ปฏิเสธ</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">ไม่มีคำขอสมัครสมาชิก</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ฟอร์มเพิ่มสมาชิก -->
        <h2 class="h4 mb-3"><i class="fas fa-user-plus me-2"></i>เพิ่มสมาชิก</h2>
        <form method="POST" class="mb-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="username" class="form-label"><i class="fas fa-user me-2"></i>ชื่อผู้ใช้</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="ชื่อผู้ใช้" required aria-describedby="usernameHelp">
                    <div id="usernameHelp" class="form-text">กรอกชื่อผู้ใช้สำหรับสมาชิกใหม่</div>
                </div>
                <div class="col-md-4">
                    <label for="password" class="form-label"><i class="fas fa-lock me-2"></i>รหัสผ่าน</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="รหัสผ่าน" required aria-describedby="passwordHelp">
                    <div id="passwordHelp" class="form-text">กรอกรหัสผ่านสำหรับสมาชิกใหม่</div>
                </div>
                <div class="col-md-4">
                    <label for="role" class="form-label"><i class="fas fa-shield-alt me-2"></i>สิทธิ์</label>
                    <select name="role" id="role" class="form-select" required aria-describedby="roleHelp">
                        <option value="user">ผู้ใช้ทั่วไป</option>
                        <option value="admin">ผู้ดูแล</option>
                    </select>
                    <div id="roleHelp" class="form-text">เลือกสิทธิ์สำหรับสมาชิก</div>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" name="add_member" class="btn btn-success"><i class="fas fa-plus-circle me-2"></i>เพิ่มสมาชิก</button>
                </div>
            </div>
        </form>

        <!-- ตารางรายชื่อสมาชิก -->
        <h2 class="h4 mb-3"><i class="fas fa-table me-2"></i>รายชื่อสมาชิก</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>สิทธิ์</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= $row['role'] === 'admin' ? 'ผู้ดูแล' : 'ผู้ใช้ทั่วไป' ?></td>
                            <td>
                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันการลบ?');"><i class="fas fa-trash me-2"></i>ลบ</a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <div class="input-group d-inline-flex align-items-center">
                                        <input type="password" name="new_password" class="form-control d-inline w-auto" placeholder="รหัสผ่านใหม่" required aria-describedby="newPasswordHelp_<?= $row['id'] ?>">
                                        <button type="submit" name="reset_password" class="btn btn-sm btn-warning"><i class="fas fa-key me-2"></i>รีเซ็ต</button>
                                    </div>
                                    <div id="newPasswordHelp_<?= $row['id'] ?>" class="form-text">กรอกรหัสผ่านใหม่สำหรับผู้ใช้</div>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <div class="input-group d-inline-flex align-items-center">
                                        <select name="new_role" class="form-select d-inline w-auto" required aria-describedby="newRoleHelp_<?= $row['id'] ?>">
                                            <option value="user" <?= $row['role'] === 'user' ? 'selected' : '' ?>>ผู้ใช้ทั่วไป</option>
                                            <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>ผู้ดูแล</option>
                                        </select>
                                        <button type="submit" name="edit_role" class="btn btn-sm btn-info"><i class="fas fa-edit me-2"></i>แก้ไข</button>
                                    </div>
                                    <div id="newRoleHelp_<?= $row['id'] ?>" class="form-text">เลือกสิทธิ์ใหม่สำหรับผู้ใช้</div>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- ระบบแบ่งหน้า -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="ก่อนหน้า"><i class="fas fa-chevron-left"></i></a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="ถัดไป"><i class="fas fa-chevron-right"></i></a>
                </li>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        particlesJS("particles-js", {
            particles: {
                number: { value: 60, density: { enable: true, value_area: 800 } },
                color: { value: "#5e2a96" },
                shape: { type: "circle" },
                opacity: { value: 0.4, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: "#5e2a96", opacity: 0.3, width: 1 },
                move: { enable: true, speed: 2, direction: "none", random: false, straight: false, out_mode: "out" }
            },
            interactivity: {
                detect_on: "canvas",
                events: { onhover: { enable: true, mode: "repulse" }, onclick: { enable: true, mode: "push" }, resize: true },
                modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
            },
            retina_detect: true
        });

        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.querySelector(".sidebar");
            const toggle = document.querySelector(".sidebar-toggle");
            if (sidebar && toggle) {
                toggle.addEventListener("click", function () {
                    sidebar.classList.toggle("active");
                });
            }

            document.addEventListener("keydown", function (event) {
                if (event.key === "Escape" && sidebar) {
                    sidebar.classList.remove("active");
                }
            });

            const rows = document.querySelectorAll(".table tbody tr");
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add("visible");
                }, index * 100);
            });

            window.addEventListener("resize", function () {
                const table = document.querySelector(".table");
                if (table) table.style.width = "100%";
                if (sidebar && window.innerWidth > 992) sidebar.classList.remove("active");
            });

            // จัดการปุ่มคัดลอกลิงก์
            const copyLinkBtn = document.getElementById('copyLinkBtn');
            if (copyLinkBtn) {
                copyLinkBtn.addEventListener('click', function () {
                    const link = copyLinkBtn.getAttribute('data-link');
                    navigator.clipboard.writeText(link).then(() => {
                        // แสดงแจ้งเตือนเมื่อคัดลอกสำเร็จ
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show';
                        alert.innerHTML = `
                            <i class='fas fa-check-circle me-2'></i>คัดลอกลิงก์สำเร็จ!
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        `;
                        document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
                        // ลบแจ้งเตือนหลัง 3 วินาที
                        setTimeout(() => alert.remove(), 3000);
                    }).catch(err => {
                        console.error('Failed to copy link:', err);
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-danger alert-dismissible fade show';
                        alert.innerHTML = `
                            <i class='fas fa-exclamation-circle me-2'></i>ไม่สามารถคัดลอกลิงก์ได้
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        `;
                        document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
                        setTimeout(() => alert.remove(), 3000);
                    });
                });
            }
        });
    </script>
</body>
</html>
