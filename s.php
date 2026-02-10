<?php
// login_logs.php
session_start();
include 'db.php';
include 'auth.php';

// Check admin access
checkLogin('admin');

// Database functions
function get_total_login_logs() {
    global $conn;
    $result = $conn->query("SELECT COUNT(*) AS total FROM login_logs");
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    return $row['total'];
}

function get_login_logs($limit, $offset) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM login_logs ORDER BY login_time DESC LIMIT ? OFFSET ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result();
}

// Fetch login logs with pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_logs = get_total_login_logs();
$log_result = get_login_logs($limit, $offset);
$total_pages = ceil($total_logs / $limit);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกการลงชื่อเข้าใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
        h2.h4 {
            color: #4b0082;
            font-weight: 600;
            font-size: 1.5rem;
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
        .pagination .pagination-control {
            background: #f0e6ff;
            border: none;
            color: #4b0082;
            margin: 0 5px;
            border-radius: 50%;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .pagination .pagination-control:hover {
            background: #5e2a96;
            color: #fff;
        }
        .pagination .page-item.active .page-link {
            background: #5e2a96;
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
            z-index: ﻿namespace
1000;
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
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <div class="sidebar">
        <div class="sidebar-content">
            <a href="e-Book.php" class="sidebar-item"><i class="fas fa-home"></i> หน้าหลัก</a>
            <a href="UMS.php" class="sidebar-item"><i class="fas fa-users"></i> ระบบจัดการสมาชิก</a>
            <a href="login_logs.php" class="sidebar-item"><i class="fas fa-sign-in-alt"></i> บันทึกการลงชื่อเข้าใช้</a>
            <a href="stats.php" class="sidebar-item"><i class="fas fa-chart-line"></i> สถิติผู้เยี่ยมชมเว็บไซต์</a>
            <a href="hidden_items.php" class="sidebar-item"><i class="bi bi-archive"></i> กู้คืนข้อมูล</a>
            <a href="logout.php" class="sidebar-item"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        </div>
    </div>
    <div class="sidebar-toggle"><i class="fas fa-bars"></i></div>

    <div class="container-fluid mt-5">
        <h1><i class="fas fa-sign-in-alt me-2"></i>บันทึกการลงชื่อเข้าใช้</h1>
        <p class="text-center">จำนวนบันทึกทั้งหมด: <strong><?= $total_logs ?></strong> รายการ</p>

        <!-- Login Logs Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>สิทธิ์</th>
                        <th>เวลาเข้า</th>
                        <th>เวลาออก</th>
                        <th>ระยะเวลา (วินาที)</th>
                        <th>ช่วงเวลา</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($log_result->num_rows > 0): ?>
                        <?php while ($log = $log_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td><?= htmlspecialchars($log['username']) ?></td>
                                <td><?= $log['role'] === 'admin' ? 'ผู้ดูแล' : 'ผู้ใช้ทั่วไป' ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($log['login_time'])) ?></td>
                                <td><?= $log['logout_time'] ? date('d/m/Y H:i:s', strtotime($log['logout_time'])) : 'ยังไม่ได้ออก' ?></td>
                                <td><?= $log['duration'] ? $log['duration'] : 'N/A' ?></td>
                                <td><?= htmlspecialchars($log['session_period'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">ไม่มีบันทึกการลงชื่อเข้าใช้</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Login log navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="pagination-control" href="?page=<?= $page - 1 ?>" aria-label="ก่อนหน้า"><i class="fas fa-chevron-left"></i></a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="pagination-control" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="pagination-control" href="?page=<?= $page + 1 ?>" aria-label="ถัดไป"><i class="fas fa-chevron-right"></i></a>
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
        });
    </script>
</body>
</html>