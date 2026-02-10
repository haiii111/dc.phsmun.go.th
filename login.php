<?php
// login.php
session_start();
include 'db.php';
include 'auth.php';

$reset_success = null;
$reset_error = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (login($username, $password)) {
            $redirect = isset($_GET['redirect']) ? urldecode($_GET['redirect']) : 'e-Book.php';
            header("Location: $redirect");
            exit;
        } else {
            $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    }

    if (isset($_POST['reset_password'], $_POST['user_id']) && isAdmin()) {
        $id = $_POST['user_id'];
        $new_password = password_hash("123456", PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("si", $new_password, $id);
        
        if ($stmt->execute()) {
            $reset_success = "รีเซ็ตรหัสผ่านสำเร็จ! รหัสผ่านใหม่คือ 123456";
        } else {
            $reset_error = "เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;600;700&family=Noto+Sans+Thai:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
        <style>
        :root {
            color-scheme: light;
            --bg-1: #faf7ff;
            --bg-2: #f5f3ff;
            --surface: #ffffff;
            --surface-2: #f8fafc;
            --primary: #8b5cf6;
            --primary-2: #a78bfa;
            --text: #1f2937;
            --muted: #6b7280;
            --border: rgba(139, 92, 246, 0.2);
            --shadow: 0 12px 28px rgba(139, 92, 246, 0.18);
        }
        body {
            font-family: "Noto Sans Thai", system-ui, -apple-system, sans-serif;
            background:
                radial-gradient(1200px 600px at 10% -10%, rgba(139, 92, 246, 0.18), transparent 60%),
                radial-gradient(1000px 500px at 100% 0%, rgba(167, 139, 250, 0.22), transparent 55%),
                linear-gradient(180deg, #ffffff, var(--bg-2));
            color: var(--text);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-size: 1rem;
            display: flex;
            flex-direction: column;
        }
        #particles-js {
            display: none;
        }
        .container-fluid {
            max-width: 520px;
            padding: 24px;
            margin: 32px auto;
            background: var(--surface);
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            position: relative;
            z-index: 10;
        }
        h1 {
            color: var(--primary);
            font-family: "Kanit", "Noto Sans Thai", system-ui, -apple-system, sans-serif;
            font-weight: 700;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
            letter-spacing: 0.3px;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.4);
            border-radius: 10px;
            padding: 14px 16px;
            position: relative;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 10px;
            padding: 14px 16px;
            position: relative;
        }
        .alert .btn-close {
            background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%231f2937'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707A1 1 0 01.293.293z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            opacity: 1;
            font-size: 1.1rem;
            padding: 0.75rem;
            width: 1em;
            height: 1em;
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }
        .form-label {
            color: var(--text);
            font-weight: 600;
        }
        .form-control, .input-group-text {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            transition: all 0.2s;
            font-weight: 500;
            height: 40px;
            font-size: 0.95rem;
            box-shadow: inset 0 1px 2px rgba(139, 92, 246, 0.08);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            background: #fff;
        }
        .form-control::placeholder {
            color: var(--muted);
        }
        .input-group-text {
            background: #f5f3ff;
            color: #4c1d95;
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: #ffffff;
            border-radius: 999px;
            padding: 8px 16px;
            transition: all 0.2s ease;
            border: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            font-size: 0.95rem;
            box-shadow: 0 6px 14px rgba(139, 92, 246, 0.2);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(139, 92, 246, 0.28);
        }
        .btn:focus-visible,
        .form-control:focus-visible {
            outline: 3px solid rgba(139, 92, 246, 0.25);
            outline-offset: 2px;
        }
        .sidebar {
            background: #ffffff;
            border-right: 1px solid var(--border);
            position: fixed;
            top: 0;
            left: -260px;
            width: 260px;
            max-width: 80vw;
            height: 100%;
            transition: left 0.3s ease-in-out;
            z-index: 1000;
            box-shadow: 8px 0 24px rgba(139, 92, 246, 0.18);
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
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 0 10px 10px 0;
            z-index: 1001;
            transition: left 0.3s ease-in-out;
            box-shadow: 0 8px 16px rgba(139, 92, 246, 0.25);
        }
        .sidebar.active ~ .sidebar-toggle {
            left: 260px;
        }
        .sidebar-toggle:hover {
            background: #7c3aed;
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
            color: #4c1d95;
            padding: 10px 12px;
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: background 0.2s, transform 0.2s;
            font-weight: 600;
        }
        .sidebar-item:hover {
            background: rgba(139, 92, 246, 0.12);
            color: var(--primary);
            transform: translateX(4px);
        }
        .sidebar-item i {
            font-size: 1.1rem;
            color: var(--primary);
        }
        @media (max-width: 576px) {
            .container-fluid {
                margin: 16px;
                padding: 18px;
            }
            h1 {
                font-size: 1.7rem;
            }
        }
    </style>
    <link href='css/styles.css' rel='stylesheet'>
</head>
<body>
    <div id="particles-js"></div>
    <div class="sidebar">
        <div class="sidebar-content">
            <a href="e-Book.php" class="sidebar-item"><i class="fas fa-home"></i> หน้าหลัก</a>
            <a href="UMS.php" class="sidebar-item"><i class="fas fa-users"></i> ระบบจัดการสมาชิก</a>
            <a href="login_logs.php" class="sidebar-item"><i class="fas fa-sign-in-alt"></i> บันทึกการลงชื่อเข้าใช้</a>
            <a href="stats.php" class="sidebar-item"><i class="fas fa-chart-line"></i> สถิติผู้เยี่ยมชมเว็บไซต์</a>
            <?php if (isAdmin()): ?>
                <a href="hidden_items.php" class="sidebar-item"><i class="bi bi-archive"></i> กู้คืนข้อมูล</a>
                <a href="#" id="openPopup" class="sidebar-item"><i class="fas fa-user-lock"></i> เข้าสู่ระบบสำหรับเจ้าหน้าที่</a>
            <?php endif; ?>
            <a href="logout.php" class="sidebar-item"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="sidebar-toggle"><i class="fas fa-bars"></i></div>

    <div class="container-fluid mt-5">
        <h1><i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ</h1>

        <!-- Alerts -->
        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($reset_success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($reset_success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($reset_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($reset_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <form method="POST" class="p-4">
            <div class="mb-3">
                <label for="username" class="form-label"><i class="fas fa-user me-2"></i>ชื่อผู้ใช้</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="กรอกชื่อผู้ใช้" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><i class="fas fa-lock me-2"></i>รหัสผ่าน</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="กรอกรหัสผ่าน" required>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
                </button>
            </div>
        </form>
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

            window.addEventListener("resize", function () {
                if (sidebar && window.innerWidth > 992) sidebar.classList.remove("active");
            });
        });
    </script>
</body>
</html>


