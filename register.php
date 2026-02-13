<?php
session_start();
include 'db.php';

$success = null;
$error = null;
$debugDb = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $passwordRaw = trim($_POST['password'] ?? '');

    if ($username === '' || $passwordRaw === '') {
        $error = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    } else {
        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);

        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? UNION SELECT id FROM pending_users WHERE username = ?");
        if (!$check_stmt) {
            $error = "เตรียมคำสั่งตรวจสอบไม่สำเร็จ: " . htmlspecialchars($conn->error);
        } else {
            $check_stmt->bind_param("ss", $username, $username);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $error = "ชื่อผู้ใช้นี้มีอยู่แล้ว กรุณาใช้ชื่ออื่น";
            }

            $check_stmt->close();
        }

        if (!isset($error)) {
            $stmt = $conn->prepare("INSERT INTO pending_users (username, password, role) VALUES (?, ?, 'user')");
            if (!$stmt) {
                $error = "เตรียมคำสั่งบันทึกไม่สำเร็จ: " . htmlspecialchars($conn->error);
            } else {
                $stmt->bind_param("ss", $username, $password);
                if ($stmt->execute() && $stmt->affected_rows === 1) {
                    $success = "สมัครสมาชิกสำเร็จ! กรุณารอการอนุมัติจากผู้ดูแลระบบ";
                } else {
                    $error = "บันทึกไม่สำเร็จ กรุณาลองใหม่อีกครั้ง";
                }
                $stmt->close();
            }
        }
    }
}

$dbRow = $conn->query("SELECT DATABASE() AS db");
if ($dbRow && $row = $dbRow->fetch_assoc()) {
    $debugDb = $row['db'];
    $dbRow->free();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
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
            background: transparent;
            color: var(--text);
            min-height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 520px;
            padding: 24px;
            background: transparent;
            border-radius: 16px;
            box-shadow: none;
            border: none;
        }
        h1 {
            color: var(--primary-3);
            font-weight: 700;
            text-align: center;
            margin-bottom: 1.25rem;
        }
        .form-control {
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            height: 40px;
            padding: 8px 12px;
            box-shadow: inset 0 1px 2px rgba(139, 92, 246, 0.08);
        }
        .form-control:focus {
            border-color: var(--primary-2);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: #fff;
            border-radius: 999px;
            border: none;
            font-weight: 600;
            height: 40px;
            box-shadow: 0 6px 14px rgba(139, 92, 246, 0.2);
        }
        .btn-primary:hover {
            box-shadow: 0 10px 18px rgba(139, 92, 246, 0.28);
        }
        .alert {
            border-radius: 10px;
        }
        a.text-decoration-none { color: var(--primary-3); font-weight: 600; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1><i class="fas fa-user-plus me-2"></i>สมัครสมาชิก</h1>
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label"><i class="fas fa-user me-2"></i>ชื่อผู้ใช้</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="ชื่อผู้ใช้" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fas fa-lock me-2"></i>รหัสผ่าน</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="รหัสผ่าน" required>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus-circle me-2"></i>สมัครสมาชิก</button>
        </form>
        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none">มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
