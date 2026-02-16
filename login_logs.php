<?php
// login_logs.php
// Set timezone to Thailand (Asia/Bangkok)
date_default_timezone_set('Asia/Bangkok');

if (session_status() !== PHP_SESSION_ACTIVE) {
    if (!headers_sent()) {
        session_start();
    } else {
        exit('Unable to start session because output was sent too early.');
    }
}
include 'db.php';
include 'auth.php';

// Check admin access
checkLogin('admin');

$roleBanner = '';
if (isAdmin()) {
    $roleBanner = '<div class="role-banner role-admin"><i class="fas fa-crown"></i>ยินดีต้อนรับ ผู้ดูแลระบบ</div>';
}

// Database functions
function get_total_unique_users($filters = []) {
    global $conn;
    $query = "SELECT COUNT(DISTINCT username) AS total FROM login_logs WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($filters['username'])) {
        $query .= " AND username LIKE ?";
        $params[] = '%' . $filters['username'] . '%';
        $types .= 's';
    }
    if (!empty($filters['ip_address'])) {
        $query .= " AND ip_address LIKE ?";
        $params[] = '%' . $filters['ip_address'] . '%';
        $types .= 's';
    }
    if (!empty($filters['date_start'])) {
        $query .= " AND DATE(login_time) >= ?";
        $params[] = $filters['date_start'];
        $types .= 's';
    }
    if (!empty($filters['date_end'])) {
        $query .= " AND DATE(login_time) <= ?";
        $params[] = $filters['date_end'];
        $types .= 's';
    }

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['total'];
}

function get_login_logs($limit, $offset, $filters = []) {
    global $conn;
    $query = "SELECT id, user_id, username, role, login_time, logout_time, duration, session_period, ip_address, logout_reason FROM login_logs WHERE 1=1";
    $params = [];
    $types = '';

    if (!empty($filters['username'])) {
        $query .= " AND username LIKE ?";
        $params[] = '%' . $filters['username'] . '%';
        $types .= 's';
    }
    if (!empty($filters['ip_address'])) {
        $query .= " AND ip_address LIKE ?";
        $params[] = '%' . $filters['ip_address'] . '%';
        $types .= 's';
    }
    if (!empty($filters['date_start'])) {
        $query .= " AND DATE(login_time) >= ?";
        $params[] = $filters['date_start'];
        $types .= 's';
    }
    if (!empty($filters['date_end'])) {
        $query .= " AND DATE(login_time) <= ?";
        $params[] = $filters['date_end'];
        $types .= 's';
    }

    $query .= " ORDER BY login_time DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

// Process filter inputs
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['username'])) {
        $filters['username'] = $_GET['username'];
    }
    if (!empty($_GET['ip_address'])) {
        $filters['ip_address'] = $_GET['ip_address'];
    }
    if (!empty($_GET['date_start'])) {
        $filters['date_start'] = $_GET['date_start'];
    }
    if (!empty($_GET['date_end'])) {
        $filters['date_end'] = $_GET['date_end'];
    }
}

// Fetch login logs with pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$total_users = get_total_unique_users($filters);
$log_result = get_login_logs($limit, $offset, $filters);
$total_pages = ceil($total_users / $limit);
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
            line-height: 1.7;
        }
        #particles-js {
            display: none;
        }
        .container-fluid {
            max-width: 100%;
            width: 100%;
            padding: 20px;
            margin: 0;
            background: var(--surface);
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 2px solid rgba(139, 92, 246, 0.45);
        }
        h1 {
            color: var(--primary-3);
            font-weight: 700;
            font-size: 2.2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .table {
            background: var(--surface);
            border-radius: 12px;
            border: 2px solid rgba(139, 92, 246, 0.45);
            width: 100%;
            table-layout: auto;
            overflow: hidden;
        }
        .table th, .table td {
            padding: 12px;
            color: var(--text);
            vertical-align: middle;
            text-align: center;
            font-size: 0.95rem;
            border: 1px solid rgba(139, 92, 246, 0.35);
            line-height: 1.5;
        }
        .table th {
            background: linear-gradient(135deg, #ede9fe, #e9d5ff);
            color: #4c1d95;
            font-weight: 700;
        }
        .table tbody tr {
            transition: background 0.2s ease, transform 0.2s ease;
            opacity: 0;
            transform: translateY(20px);
        }
        .table tbody tr.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }
        .table tbody tr:hover {
            background: #f5f3ff;
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
            background: #ffffff;
            border-right: 1px solid var(--border);
            position: absolute;
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
            position: absolute;
            top: 20px;
            left: 0;
            width: 40px;
            height: 40px;
            background: var(--primary-2);
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
            background: var(--primary-3);
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
            background: #f5f3ff;
            color: var(--primary-3);
            transform: translateX(4px);
        }
        .sidebar-item i {
            font-size: 1.1rem;
            color: var(--primary-2);
        }
        .form-control, .form-select {
            background: #fff;
            border: 2px solid rgba(139, 92, 246, 0.45);
            color: var(--text);
            border-radius: 10px;
            transition: all 0.2s;
            font-weight: 600;
            height: 40px;
            font-size: 0.95rem;
            padding: 8px 12px;
            box-shadow: inset 0 1px 2px rgba(139, 92, 246, 0.12);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-2);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            background: #fff;
        }
        .form-control::placeholder {
            color: #999;
        }
        .form-label {
            color: #333;
            font-weight: 500;
        }
        .btn {
            border-radius: 0 0 0 12px;
            padding: 6px 14px;
            transition: all 0.2s ease;
            border: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            font-size: 0.9rem;
            gap: 6px;
            max-width: 160px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.18);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: #ffffff;
        }
        .btn-primary:hover {
            box-shadow: 0 10px 18px rgba(139, 92, 246, 0.28);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            color: #ffffff;
        }
        .form-card {
            background: var(--surface-2);
            border: 2px solid rgba(139, 92, 246, 0.45);
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 6px 16px rgba(139, 92, 246, 0.12);
        }
        .popup-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1100;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease-in-out, visibility 0.3s;
        }
        .popup-overlay.show {
            visibility: visible;
            opacity: 1;
        }
        .popup-box {
            background: #fff;
            border: 2px solid rgba(139, 92, 246, 0.45);
            padding: 20px;
            border-radius: 14px;
            width: 90%;
            max-width: 420px;
            box-shadow: var(--shadow);
        }
        .popup-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary-3);
        }
        .popup-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .popup-header .btn-close {
            margin-left: auto;
            width: 1.25rem;
            height: 1.25rem;
        }
        .role-banner {
            position: absolute;
            top: 0;
            right: 0;
            z-index: 1100;
            padding: 10px 16px;
            border-radius: 0 0 0 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow);
            border: 2px solid rgba(139, 92, 246, 0.45);
            background: var(--surface);
            color: var(--primary-3);
        }
        .role-banner i {
            font-size: 0.95rem;
        }
        .role-admin {
            background: linear-gradient(135deg, #ede9fe, #f5f3ff);
        }
        @media (max-width: 992px) {
            .container-fluid { padding: 16px; margin: 16px; }
            .sidebar { width: 220px; left: -220px; }
            .sidebar.active ~ .sidebar-toggle { left: 220px; }
            h1 { font-size: 2rem; }
            .table th, .table td { font-size: 0.9rem; padding: 10px; }
            .form-control, .form-select, .btn { width: 100%; max-width: 100%; }
            .btn { height: 36px; font-size: 0.85rem; }
        }
        @media (max-width: 576px) {
            body { font-size: 0.92rem; }
            .container-fluid { padding: 14px; margin: 12px; }
            .table th, .table td { font-size: 0.85rem; padding: 8px; }
            .btn { height: 34px; font-size: 0.82rem; }
            .sidebar-toggle { width: 36px; height: 36px; }
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <?= $roleBanner ?>
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

    <div id="customPopup" class="popup-overlay">
        <div class="popup-box">
            <div class="popup-header">
                <h5 class="popup-title"><i class="fas fa-sign-in-alt me-2"></i>ยืนยันการเข้าสู่ระบบ</h5>
                <button type="button" class="btn-close" id="closePopup" aria-label="Close"></button>
            </div>
            <div class="popup-body">คุณต้องการเข้าสู่ระบบสำหรับเจ้าหน้าที่หรือไม่?</div>
            <div class="popup-footer d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-secondary" id="closePopupBtn"><i class="fas fa-times me-2"></i>ยกเลิก</button>
                <a href="login.php" class="btn btn-primary"><i class="fas fa-check me-2"></i>ยืนยัน</a>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-5">
        <h1><i class="fas fa-sign-in-alt me-2"></i>บันทึกการลงชื่อเข้าใช้</h1>
        
        <!-- Filter Form -->
        <form method="GET" class="mb-4 form-card">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="username" class="form-label"><i class="fas fa-user me-2"></i>ชื่อผู้ใช้</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="กรอกชื่อผู้ใช้" value="<?= htmlspecialchars($filters['username'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="ip_address" class="form-label"><i class="fas fa-network-wired me-2"></i>ที่อยู่ IP</label>
                    <input type="text" class="form-control" id="ip_address" name="ip_address" placeholder="กรอก IP" value="<?= htmlspecialchars($filters['ip_address'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_start" class="form-label"><i class="fas fa-calendar-alt me-2"></i>วันที่เริ่มต้น</label>
                    <input type="date" class="form-control" id="date_start" name="date_start" value="<?= htmlspecialchars($filters['date_start'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_end" class="form-label"><i class="fas fa-calendar-alt me-2"></i>วันที่สิ้นสุด</label>
                    <input type="date" class="form-control" id="date_end" name="date_end" value="<?= htmlspecialchars($filters['date_end'] ?? '') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="d-flex flex-column flex-sm-row gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-grow-1"><i class="fas fa-filter me-2"></i>กรอง</button>
                        <a href="login_logs.php" class="btn btn-secondary flex-grow-1"><i class="fas fa-undo me-2"></i>ล้างตัวกรอง</a>
                    </div>
                </div>
            </div>
        </form>

        <p class="text-center">จำนวนผู้ใช้ทั้งหมด: <strong><?= $total_users ?></strong> ราย</p>

        <!-- Login Logs Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อผู้ใช้</th>
                        <th>บทบาท</th>
                        <th>เวลาเข้า</th>
                        <th>เวลาออก</th>
                        <th>ระยะเวลา (วินาที)</th>
                        <th>ช่วงเวลา</th>
                        <th>ที่อยู่ IP</th>
                        <th>เหตุผลการออก</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($log_result->num_rows > 0): ?>
                        <?php while ($log = $log_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['id']) ?></td>
                                <td><?= htmlspecialchars($log['username']) ?></td>
                                <td><?= $log['role'] === 'admin' ? 'ผู้ดูแล' : 'ผู้ใช้ทั่วไป' ?></td>
                                <td><?= date('d/m/Y H:i:s', strtotime($log['login_time'])) ?></td>
                                <td><?= $log['logout_time'] ? date('d/m/Y H:i:s', strtotime($log['logout_time'])) : 'ยังไม่ได้ออก' ?></td>
                                <td><?= $log['duration'] ? htmlspecialchars($log['duration']) : 'N/A' ?></td>
                                <td><?= htmlspecialchars($log['session_period'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                                <td><?= $log['logout_reason'] === 'manual' ? 'ออกด้วยตนเอง' : ($log['logout_reason'] === 'auto_inactivity' ? 'ออกอัตโนมัติ (ไม่มีการใช้งาน)' : 'N/A') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">ไม่มีบันทึกการลงชื่อเข้าใช้</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Login log navigation">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&<?= http_build_query($filters) ?>" aria-label="ก่อนหน้า"><i class="fas fa-chevron-left"></i></a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($filters) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&<?= http_build_query($filters) ?>" aria-label="ถัดไป"><i class="fas fa-chevron-right"></i></a>
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

            const openPopup = document.getElementById("openPopup");
            const closePopup = document.getElementById("closePopup");
            const closePopupBtn = document.getElementById("closePopupBtn");
            const popupOverlay = document.getElementById("customPopup");
            function closePopupFunc() {
                if (popupOverlay) popupOverlay.classList.remove("show");
            }
            if (openPopup) {
                openPopup.addEventListener("click", function (event) {
                    event.preventDefault();
                    popupOverlay.classList.add("show");
                });
            }
            if (closePopup) closePopup.addEventListener("click", closePopupFunc);
            if (closePopupBtn) closePopupBtn.addEventListener("click", closePopupFunc);
            if (popupOverlay) {
                popupOverlay.addEventListener("click", function (event) {
                    if (event.target === popupOverlay) closePopupFunc();
                });
            }

            document.addEventListener("keydown", function (event) {
                if (event.key === "Escape" && sidebar) {
                    sidebar.classList.remove("active");
                }
                if (event.key === "Escape") {
                    closePopupFunc();
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

            // Inactivity Timeout
            const INACTIVITY_TIMEOUT = 60000; // 1 minute in milliseconds
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


