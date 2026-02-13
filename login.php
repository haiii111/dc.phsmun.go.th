<?php
// login.php
session_start();
// include 'db.php';
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
            background: linear-gradient(-45deg, #e6e6fa, #f0e6ff, #f5e6ff, #e6e6fa);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #2d2d2d;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
            box-sizing: border-box;
            font-size: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
            z-index: 2;
        }
        .container-fluid {
            max-width: 500px;
            padding: 20px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
            width: min(92vw, 500px);
        }
        .container-fluid.mt-5 { margin-top: 0 !important; }
        h1 {
            color: #4b0082;
            font-weight: 700;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            color: blue;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            position: relative;
        }
        .alert-danger {
            background: rgba(255, 85, 85, 0.1);
            color: #dc3545;
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 15px;
            position: relative;
        }
        .alert .btn-close {
            background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%231a1a1a'%3e%3cpath d='M.293.293a1 1 0 1 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707A1 1 0 0 1 .293.293Z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            color: #1a1a1a;
            opacity: 1;
            font-size: 1.1rem;
            padding: 0.75rem;
            width: 1em;
            height: 1em;
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer

        }
        .form-control, .input-group-text {
            background: #fff;
            border: 1px solid #d1c4e9;
            color: #2d2d2d;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 400;
            height: 40px;
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #5e2a96;
            box-shadow: 0 0 8px rgba(94, 42, 150, 0.3);
            background: #fff;
        }
        .form-control::placeholder {
            color: #999;
        }
        .input-group-text {
            background: #f0e6ff;
            color: #4b0082;
        }
        .form-label {
            color: #333;
            font-weight: 500;
        }
        .btn-primary {
            background: #5e2a96;
            color: #ffffff;
            border-radius: 8px;
            padding: 8px 16px;
            transition: all 0.3s ease;
            border: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            font-size: 0.9rem;
        }
        .btn-primary:hover {
            background: #4b2078;
            box-shadow: 0 0 10px rgba(94, 42, 150, 0.5);
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
            color: #4b0082;
            padding: 10px 12px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: background 0.2s, transform 0.2s;
            font-weight: 500;
        }
        .sidebar-item:hover {
            background: #f0e6ff;
            color: #5e2a96;
            transform: translateX(5px);
        }
        .sidebar-item i {
            font-size: 1.2rem;
            color: #5e2a96;
        }
        @media (max-width: 992px) {
            .container-fluid {
                padding: 15px;
                margin: 0;
            }
            .sidebar {
                width: 220px;
                left: -220px;
            }
            .sidebar.active ~ .sidebar-toggle {
                left: 220px;
            }
            h1 {
                font-size: 1.8rem;
            }
            .form-control, .input-group-text {
                font-size: 0.85rem;
                height: 36px;
            }
            .btn-primary {
                font-size: 0.85rem;
                height: 36px;
            }
        }
        @media (max-width: 576px) {
            body {
                font-size: 0.9rem;
                padding: 16px;
            }
            .container-fluid {
                padding: 10px;
                margin: 0;
            }
            .form-control, .input-group-text {
                height: 34px;
                font-size: 0.8rem;
            }
            .btn-primary {
                height: 34px;
                font-size: 0.8rem;
                padding: 6px 12px;
            }
            .sidebar-toggle {
                width: 36px;
                height: 36px;
            }
        }
        @media (orientation: landscape) and (max-height: 500px) {
            .container-fluid {
                padding: 10px;
                margin: 0;
            }
            .form-control, .input-group-text {
                height: 34px;
                font-size: 0.85rem;
            }
            .btn-primary {
                font-size: 0.85rem;
                height: 34px;
            }
        }
        /* e-Book theme alignment overrides */
        body { background: var(--bg) !important; color: var(--text) !important; line-height: 1.7; }
        #particles-js { display: none !important; }
        .container, .container-fluid { background: var(--surface) !important; border: 1px solid var(--border) !important; box-shadow: var(--shadow) !important; border-radius: 16px !important; }
        .table th { background: linear-gradient(135deg, #ede9fe, #e9d5ff) !important; color: #4c1d95 !important; font-weight: 700 !important; }
        .btn { border-radius: 999px !important; box-shadow: 0 4px 10px rgba(139, 92, 246, 0.18) !important; }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-2)) !important; color: #ffffff !important; }
        .btn-primary:hover { box-shadow: 0 10px 18px rgba(139, 92, 246, 0.28) !important; transform: translateY(-1px); }
        .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a) !important; color: #ffffff !important; }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626) !important; color: #ffffff !important; }
        .btn-warning { background: linear-gradient(135deg, #fbbf24, #f59e0b) !important; color: #1f2937 !important; }
        .btn-info { background: linear-gradient(135deg, #38bdf8, #0ea5e9) !important; color: #ffffff !important; }
        .btn-copy { background: linear-gradient(135deg, #94a3b8, #64748b) !important; color: #ffffff !important; }
        .form-control, .form-select { border: 1px solid var(--border) !important; border-radius: 10px !important; box-shadow: inset 0 1px 2px rgba(139, 92, 246, 0.08) !important; }
        .form-control:focus, .form-select:focus { border-color: var(--primary-2) !important; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2) !important; }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="sidebar">
        <div class="sidebar-content">
            <a href="e-Book.php" class="sidebar-item"><i class="fas fa-home"></i> หน้าหลัก</a>
            <?php if (isAdmin()): ?>
                <a href="UMS.php" class="sidebar-item"><i class="fas fa-users"></i> ระบบจัดการสมาชิก</a>
                <a href="login_logs.php" class="sidebar-item"><i class="fas fa-sign-in-alt"></i> บันทึกการลงชื่อเข้าใช้</a>
                <a href="stats.php" class="sidebar-item"><i class="fas fa-chart-line"></i> สถิติผู้เยี่ยมชมเว็บไซต์</a>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <a href="hidden_items.php" class="sidebar-item"><i class="bi bi-archive"></i> กู้คืนข้อมูล</a>
                <a href="#" id="openPopup" class="sidebar-item"><i class="fas fa-user-lock"></i> เข้าสู่ระบบสำหรับเจ้าหน้าที่</a>
            <?php endif; ?>
            <?php if (isAdmin()): ?>
                <a href="logout.php" class="sidebar-item"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
            <?php endif; ?>
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
