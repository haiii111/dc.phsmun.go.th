<?php
session_start();
include 'db.php';
include 'auth.php';

checkLogin();
if (!isAdmin() && !isUser()) {
    header('Location: e-Book.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $details = trim($_POST['details']);
    $image = $_FILES['image'];
    $pdf_file = $_FILES['pdf_file'];

    // // ตรวจสอบความถูกต้องของฟอร์ม
    // if (empty($name)) {
    //     $errors[] = 'กรุณากรอกชื่อ';
    // }
    // if (empty($details)) {
    //     $errors[] = 'กรุณากรอกรายละเอียด';
    // }

    // ตรวจสอบการอัปโหลดไฟล์
    $imageName = '';
    $pdfName = '';

    if ($image['error'] === UPLOAD_ERR_OK) {
        $imageExt = pathinfo($image['name'], PATHINFO_EXTENSION);
        $imageName = uniqid('img_') . '.' . $imageExt;
        $imagePath = __DIR__ . '/uploads/' . $imageName;

        if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
            $errors[] = 'อัปโหลดรูปภาพล้มเหลว';
        }
    }

    if ($pdf_file['error'] === UPLOAD_ERR_OK) {
        $pdfExt = pathinfo($pdf_file['name'], PATHINFO_EXTENSION);
        $pdfName = uniqid('pdf_') . '.' . $pdfExt;
        $pdfPath = __DIR__ . '/uploads/' . $pdfName;

        if (!move_uploaded_file($pdf_file['tmp_name'], $pdfPath)) {
            $errors[] = 'อัปโหลดไฟล์ PDF ล้มเหลว';
        }
    }

    // บันทึกข้อมูลลงฐานข้อมูล
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO items (name, details, image, pdf_file, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssss', $name, $details, $imageName, $pdfName);

        if ($stmt->execute()) {
            header('Location: e-Book.php?success=เพิ่มข้อมูลสำเร็จ');
            exit();
        } else {
            $errors[] = 'บันทึกข้อมูลล้มเหลว: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูล</title>
    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <style>
        body {
            font-family: 'Noto Sans Thai', sans-serif;
            background: linear-gradient(-45deg, #e6e6fa, #f0e6ff, #f5e6ff, #e6e6fa);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #2d2d2d;
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
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        .container {
            max-width: 95%;
            padding: 15px;
            margin: 15px auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #4b0082;
            font-weight: 700;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
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
        .form-control, .form-control-file {
            background: #fff;
            border: 1px solid #d1c4e9;
            color: #2d2d2d;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 400;
            height: 36px;
            font-size: 0.9rem;
            padding: 8px;
        }
        .form-control:focus, .form-control-file:focus {
            border-color: #5e2a96;
            box-shadow: 0 0 8px rgba(94, 42, 150, 0.3);
            background: #fff;
        }
        .form-control::placeholder {
            color: #6c757d;
            font-weight: 400;
        }
        .form-label {
            color: #2d2d2d;
            font-weight: 500;
        }
        .form-text {
            color: #4b0082;
        }
        .btn {
            border-radius: 8px;
            padding: 6px 12px;
            transition: all 0.3s ease;
            border: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            font-size: 0.9rem;
            gap: 6px;
            max-width: 150px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }
        .btn-success {
            background: #2e7d32;
            color: #ffffff;
        }
        .btn-success:hover {
            background: #25632a;
            box-shadow: 0 0 10px rgba(46, 125, 50, 0.5);
        }
        .btn-secondary {
            background: #6c757d;
            color: #ffffff;
        }
        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 0 10px rgba(108, 117, 125, 0.5);
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
            .container {
                padding: 12px;
                margin: 10px;
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
        }
        @media (max-width: 576px) {
            body {
                font-size: 0.9rem;
            }
            .container {
                padding: 10px;
                margin: 8px;
            }
            .form-control, .form-control-file {
                height: 34px;
                font-size: 0.85rem;
            }
            .btn {
                height: 32px;
                font-size: 0.8rem;
                padding: 6px 10px;
                max-width: 100%;
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
            .container {
                padding: 10px;
            }
            .form-control, .form-control-file {
                width: auto;
                flex-grow: 1;
            }
            .btn {
                font-size: 0.85rem;
                max-width: 120px;
            }
            .alert .btn-close {
                font-size: 1rem;
                padding: 0.5rem;
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
        .form-control, .form-control-file, .form-select { border: 1px solid var(--border) !important; border-radius: 10px !important; box-shadow: inset 0 1px 2px rgba(139, 92, 246, 0.08) !important; height: 40px !important; }
        .form-control:focus, .form-control-file:focus, .form-select:focus { border-color: var(--primary-2) !important; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2) !important; }
        /* clearer form card */
        .form-card {
            background: #ffffff;
            border: 2px solid rgba(139, 92, 246, 0.45);
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 14px 28px rgba(139, 92, 246, 0.18);
        }
        .form-label {
            color: #1f2937;
            font-weight: 600;
        }
        .form-text {
            color: #4b5563;
        }
        .form-control, .form-control-file, .form-select {
            border: 1px solid rgba(139, 92, 246, 0.35);
            box-shadow: inset 0 1px 2px rgba(139, 92, 246, 0.10);
            color: #111827;
            font-weight: 500;
        }
        .form-control:focus, .form-control-file:focus, .form-select:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.18);
        }
    </style>
</head>
<body>
    <!-- Particle Background -->
    <div id="particles-js"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-content">
            <a href="e-Book.php" class="sidebar-item"><i class="fas fa-home"></i> หน้าหลัก</a>
            <?php if (isAdmin()): ?>
            <a href="UMS.php" class="sidebar-item"><i class="fas fa-users"></i> ระบบจัดการสมาชิก</a>
            <a href="stats.php" class="sidebar-item"><i class="fas fa-chart-line"></i> สถิติผู้เยี่ยมชมเว็บไซต์</a>
            <a href="hidden_items.php" class="sidebar-item"><i class="bi bi-archive"></i> กู้คืนข้อมูล</a>
            <?php endif; ?>
            <a href="logout.php" class="sidebar-item"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="sidebar-toggle" aria-label="เปิด/ปิดเมนู">
        <i class="fas fa-bars"></i>
    </div>

    <div class="container mt-5">
        <h1 class="mb-4"><i class="fas fa-plus-circle me-2"></i>เพิ่มข้อมูล</h1>

        <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <form action="add.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label"><i class="fas fa-tag me-2"></i>ชื่อ</label>
                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" aria-describedby="nameHelp">
                <div id="nameHelp" class="form-text">กรุณากรอกชื่อที่ต้องการเพิ่ม</div>
            </div>
            <div class="mb-3">
                <label for="details" class="form-label"><i class="fas fa-info-circle me-2"></i>รายละเอียด</label>
                <textarea name="details" id="details" class="form-control" rows="4" aria-describedby="detailsHelp"><?= htmlspecialchars($_POST['details'] ?? '') ?></textarea>
                <div id="detailsHelp" class="form-text">กรุณากรอกรายละเอียดเพิ่มเติม (ไม่บังคับ)</div>
            </div>            
            <div class="mb-3">
                <label for="image" class="form-label"><i class="fas fa-image me-2"></i>อัปโหลดรูปภาพ</label>
                <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png,image/gif" aria-describedby="imageHelp">
                <div id="imageHelp" class="form-text">รองรับไฟล์ JPG, PNG, GIF (สูงสุด 20MB)</div>
            </div>
            <div class="mb-3">
                <label for="pdf_file" class="form-label"><i class="fas fa-file-pdf me-2"></i>อัปโหลดไฟล์ PDF</label>
                <input type="file" name="pdf_file" id="pdf_file" class="form-control" accept="application/pdf" aria-describedby="pdfHelp">
                <div id="pdfHelp" class="form-text">รองรับไฟล์ PDF (สูงสุด 100MB)</div>
            </div>
            <div class="d-flex gap-3">
                <a href="e-Book.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>กลับ</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>บันทึกข้อมูล</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Particles.js
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

        // Sidebar Toggle
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.querySelector(".sidebar");
            const toggle = document.querySelector(".sidebar-toggle");
            if (toggle && sidebar) {
                toggle.addEventListener("click", function () {
                    sidebar.classList.toggle("active");
                });
            }

            document.addEventListener("keydown", function (event) {
                if (event.key === "Escape" && sidebar) {
                    sidebar.classList.remove("active");
                }
            });

            document.getElementById('image').addEventListener('change', function(e) {
                const maxSize = 20 * 1024 * 1024; // 2MB
                if (e.target.files[0] && e.target.files[0].size > maxSize) {
                    alert('ขนาดไฟล์รูปภาพต้องไม่เกิน 20MB');
                    e.target.value = '';
                }
            });

                    document.getElementById('pdf_file').addEventListener('change', function(e) {
                const maxSize = 200 * 1024 * 1024; // 1MB
                if (e.target.files[0] && e.target.files[0].size > maxSize) {
                    alert('ขนาดไฟล์ PDF ต้องไม่เกิน 200MB');
                    e.target.value = '';
                }
            });
                        // Inactivity Timeout
            const INACTIVITY_TIMEOUT = 600000; // 1 minute in milliseconds
            let timeoutId;

            function resetTimeout() {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(autoLogout, INACTIVITY_TIMEOUT);
            }

            function autoLogout() {
                fetch('logout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'auto_logout=true'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => console.error('Error during auto logout:', error));
            }

            // Reset timer on user activity
            ['mousemove', 'keydown', 'click', 'scroll', 'touchstart', 'resize'].forEach(event => {
                document.addEventListener(event, resetTimeout);
            });

            // Start the timeout on page load
            resetTimeout();
     

            
        });
    </script>
</body>
</html>
