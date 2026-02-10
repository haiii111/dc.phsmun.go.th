<?php
session_start();
include 'db.php';

// ตรวจสอบว่ามีการส่งฟอร์มสมัครสมาชิก
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // ตรวจสอบว่าชื่อผู้ใช้ซ้ำในตาราง users หรือ pending_users หรือไม่
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? UNION SELECT id FROM pending_users WHERE username = ?");
    $check_stmt->bind_param("ss", $username, $username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $error = "ชื่อผู้ใช้นี้มีอยู่แล้ว กรุณาใช้ชื่ออื่น";
    } else {
        // บันทึกข้อมูลลงตาราง pending_users
        $stmt = $conn->prepare("INSERT INTO pending_users (username, password, role) VALUES (?, ?, 'user')");
        $stmt->bind_param("ss", $username, $password);
        if ($stmt->execute()) {
            $success = "สมัครสมาชิกสำเร็จ! กรุณารอการอนุมัติจากผู้ดูแลระบบ";
        } else {
            $error = "เกิดข้อผิดพลาด: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
    $check_stmt->close();
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
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <style>
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            background: linear-gradient(-45deg, #e6e6fa, #f0e6ff, #f5e6ff, #e6e6fa);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #2d2d2d;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .container {
            max-width: 500px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #4b0082;
            font-weight: 700;
            text-align: center;
        }
        .form-control {
            border: 1px solid #d1c4e9;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #5e2a96;
            box-shadow: 0 0 8px rgba(94, 42, 150, 0.3);
        }
        .btn-primary {
            background: #5e2a96;
            color: #fff;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background: #4b2078;
            box-shadow: 0 0 10px rgba(94, 42, 150, 0.5);
        }
        .alert {
            border-radius: 8px;
        }
    </style>
    <link href='css/styles.css' rel='stylesheet'>
</head>
<body>
    <div id="particles-js"></div>
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
    </script>
</body>
</html>
