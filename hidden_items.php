<?php
session_start();
include 'db.php';
include 'auth.php';

// ตรวจสอบการล็อกอินและบทบาท
if (!isset($_SESSION['role']) || $_SESSION['role'] === 'guest') {
    header('Location: login.php?redirect=' . urlencode('hidden_items.php'));
    exit();
}
if (!in_array($_SESSION['role'], ['admin', 'user'])) {
    header('Location: index.php');
    exit();
}

// สร้าง CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// จัดการการลบหลายรายการ (สำหรับแอดมิน)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected']) && $_SESSION['role'] === 'admin') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Location: hidden_items.php?error=CSRF token ไม่ถูกต้อง');
        exit();
    }

    if (isset($_POST['selected_items']) && is_array($_POST['selected_items'])) {
        $ids = array_map('intval', $_POST['selected_items']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // ดึงข้อมูลไฟล์เพื่อลบ
        $stmt = $conn->prepare("SELECT image, pdf_file FROM items WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            if (!empty($row['image']) && file_exists(__DIR__ . '/uploads/' . $row['image'])) {
                unlink(__DIR__ . '/Uploads/' . $row['image']);
            }
            if (!empty($row['pdf_file']) && file_exists(__DIR__ . '/Uploads/' . $row['pdf_file'])) {
                unlink(__DIR__ . '/Uploads/' . $row['pdf_file']);
            }
        }

        // ลบข้อมูลจากตาราง
        $stmt = $conn->prepare("DELETE FROM items WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        
        if ($stmt->execute()) {
            header('Location: hidden_items.php?success=ลบข้อมูลที่เลือกสำเร็จ');
        } else {
            header('Location: hidden_items.php?error=การลบข้อมูลล้มเหลว');
        }
        exit();
    } else {
        header('Location: hidden_items.php?error=ไม่ได้เลือกข้อมูลใดๆ');
        exit();
    }
}

// ดึงข้อมูลที่ถูกซ่อน
$result = $conn->query("SELECT * FROM items WHERE hidden = 1 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการที่ถูกซ่อน</title>
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
        /* รักษาสไตล์เดิม */
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
        .container-fluid {
            max-width: 90%;
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
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            color: #28a745;
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
            border: 1px solid #e0e0e0;
            width: 100%;
            table-layout: auto;
        }
        .table th, .table td {
            padding: 12px;
            color: #2d2d2d;
            vertical-align: middle;
            text-align: center;
            font-size: 0.95rem;
            border: 1px solid #e0e0e0;
        }
        .table th {
            background: #f0e6ff;
            color: #4b0082;
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
        .btn-sm {
            padding: 4px 8px;
            font-size: 0.85rem;
            height: 32px;
            max-width: 120px;
        }
        .btn-success {
            background: #2e7d32;
            color: #ffffff;
        }
        .btn-success:hover {
            background: #25632a;
            box-shadow: 0 0 10px rgba(46, 125, 50, 0.5);
        }
        .btn-danger {
            background: #dc3545;
            color: #ffffff;
        }
        .btn-danger:hover {
            background: #c82333;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
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
        .select-all-checkbox {
            cursor: pointer;
        }
        @media (max-width: 992px) {
            .container-fluid {
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
            .btn {
                height: 32px;
                font-size: 0.8rem;
                padding: 6px 10px;
                max-width: 100%;
            }
            .btn-sm {
                height: 30px;
                font-size: 0.75rem;
                padding: 4px 8px;
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
            .btn {
                font-size: 0.85rem;
                max-width: 120px;
            }
            .alert .btn-close {
                font-size: 1rem;
                padding: 0.5rem;
            }
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
            <a href="UMS.php" class="sidebar-item"><i class="fas fa-users"></i> ระบบจัดการสมาชิก</a>
            <a href="stats.php" class="sidebar-item"><i class="fas fa-chart-line"></i> สถิติผู้เยี่ยมชมเว็บไซต์</a>
            <a href="hidden_items.php" class="sidebar-item"><i class="bi bi-archive"></i> กู้คืนข้อมูล</a> 
            <a href="logout.php" class="sidebar-item"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="sidebar-toggle"><i class="fas fa-bars"></i></div>

    <div class="container-fluid mt-5">
        <h1><i class="fas fa-archive me-2"></i>ข้อมูลที่ถูกซ่อน</h1>

        <!-- แสดงข้อความแจ้งเตือน -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ฟอร์มสำหรับการเลือกและลบหลายรายการ -->
        <form method="POST" id="delete-form">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <th style="width: 50px;">
                                    <input type="checkbox" class="select-all-checkbox" id="select-all">
                                </th>
                            <?php endif; ?>
                            <th style="width: 50px;">ลำดับ</th>
                            <th style="width: 200px;">ชื่อ</th>
                            <th style="width: 150px;">รายละเอียด</th>
                            <th style="width: 150px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                        <?php foreach ($result as $index => $item): ?>
                        <tr>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <td>
                                    <input type="checkbox" name="selected_items[]" value="<?= $item['id'] ?>" class="item-checkbox">
                                </td>
                            <?php endif; ?>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= htmlspecialchars($item['details']) ?></td>
                            <td>
                                <a href="restore.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-undo me-2"></i>กู้คืน</a>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="delete.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลนี้?')"><i class="fas fa-trash me-2"></i>ลบ</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="<?= $_SESSION['role'] === 'admin' ? 5 : 4 ?>" class="text-center">ไม่มีข้อมูล</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($_SESSION['role'] === 'admin' && $result->num_rows > 0): ?>
                <div class="mt-3">
                    <button type="submit" name="delete_selected" class="btn btn-danger"><i class="fas fa-trash me-2"></i>ลบที่เลือก</button>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Bootstrap and Particles.js Scripts -->
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

        // Animate Table Rows and Sidebar Toggle
        document.addEventListener("DOMContentLoaded", function () {
            // Table Row Animation
            const rows = document.querySelectorAll(".table tbody tr");
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add("visible");
                }, index * 100);
            });

            // Sidebar Toggle
            const sidebar = document.querySelector(".sidebar");
            const toggle = document.querySelector(".sidebar-toggle");
            if (sidebar && toggle) {
                toggle.addEventListener("click", function () {
                    sidebar.classList.toggle("active");
                });
            }

            // Close Sidebar on Escape
            document.addEventListener("keydown", function (event) {
                if (event.key === "Escape" && sidebar) {
                    sidebar.classList.remove("active");
                }
            });

            // Select All Checkbox
            const selectAllCheckbox = document.querySelector("#select-all");
            const itemCheckboxes = document.querySelectorAll(".item-checkbox");
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener("change", function () {
                    itemCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                });
            }

            // Confirm Delete Multiple
            const deleteForm = document.querySelector("#delete-form");
            if (deleteForm) {
                deleteForm.addEventListener("submit", function (event) {
                    const checkedItems = document.querySelectorAll(".item-checkbox:checked");
                    if (checkedItems.length === 0) {
                        alert("กรุณาเลือกอย่างน้อยหนึ่งรายการ");
                        event.preventDefault();
                        return false;
                    }
                    if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการลบรายการที่เลือก?")) {
                        event.preventDefault();
                        return false;
                    }
                });
            }

            // Responsive Adjustments
            window.addEventListener("resize", function () {
                const table = document.querySelector(".table");
                if (table) table.style.width = "100%";
                if (sidebar && window.innerWidth > 992) sidebar.classList.remove("active");
            });
        });
    </script>
</body>
</html>