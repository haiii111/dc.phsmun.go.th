<?php
session_start();
date_default_timezone_set('Asia/Bangkok');
include 'auth.php'; // Include auth.php for session management

// Restrict access to Admin and User roles only
if (isGuest()) {
    header("Location: login.php?error=คุณต้องเข้าสู่ระบบในฐานะผู้ดูแลหรือผู้ใช้เพื่อดูหน้านี้");
    exit();
}

// ใช้การเชื่อมต่อจาก db.php (ผ่าน auth.php)

// Pagination and Filter Parameters
$itemsPerPageOptions = [5, 10, 20, 50];
$itemsPerPage = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 10;
if (!in_array($itemsPerPage, $itemsPerPageOptions, true)) {
    $itemsPerPage = 10;
}

$pageDaily = max(1, (int)($_GET['page_daily'] ?? 1));
$pageMonthly = max(1, (int)($_GET['page_monthly'] ?? 1));
$pageYearly = max(1, (int)($_GET['page_yearly'] ?? 1));

function build_stats_url(array $params, string $anchor = ''): string
{
    $query = http_build_query($params);
    $url = $query === '' ? '?' : '?' . $query;
    if ($anchor !== '') {
        $url .= '#' . $anchor;
    }
    return $url;
}

function build_page_range(int $current, int $total, int $window = 2): array
{
    if ($total <= 1) {
        return [1];
    }

    $maxDisplay = ($window * 2) + 5;
    if ($total <= $maxDisplay) {
        return range(1, $total);
    }

    $pages = [1];

    $start = max(2, $current - $window);
    $end = min($total - 1, $current + $window);

    if ($current <= $window + 2) {
        $start = 2;
        $end = min($total - 1, 2 + ($window * 2));
    } elseif ($current >= $total - ($window + 1)) {
        $end = $total - 1;
        $start = max(2, $total - (($window * 2) + 1));
    }

    if ($start > 2) {
        $pages[] = '...';
    }

    for ($i = $start; $i <= $end; $i++) {
        $pages[] = $i;
    }

    if ($end < $total - 1) {
        $pages[] = '...';
    }

    $pages[] = $total;

    return $pages;
}

$offsetDaily = ($pageDaily - 1) * $itemsPerPage;
$offsetMonthly = ($pageMonthly - 1) * $itemsPerPage;
$offsetYearly = ($pageYearly - 1) * $itemsPerPage;

// Daily Visits Filter
$dailyFilter = isset($_GET['daily_filter']) ? trim($_GET['daily_filter']) : '';
$dailySearch = isset($_GET['daily_search']) ? trim($_GET['daily_search']) : '';
$dailyQuery = "";
if ($dailySearch !== '') {
    $dailySearchEscaped = $conn->real_escape_string($dailySearch);
    $dailySearchLike = "'%" . $dailySearchEscaped . "%'";
    if ($dailyFilter === 'date') {
        $dailyQuery = "WHERE visit_date LIKE $dailySearchLike";
    } elseif ($dailyFilter === 'ip') {
        $dailyQuery = "WHERE ip_address LIKE $dailySearchLike";
    } else {
        $dailyQuery = "WHERE visit_date LIKE $dailySearchLike OR ip_address LIKE $dailySearchLike";
    }
}

// Monthly Visits Filter
$monthlyFilter = isset($_GET['monthly_filter']) ? trim($_GET['monthly_filter']) : '';
$monthlySearch = isset($_GET['monthly_search']) ? trim($_GET['monthly_search']) : '';
$monthlyQuery = "";
if ($monthlySearch !== '') {
    $monthlySearchEscaped = $conn->real_escape_string($monthlySearch);
    $monthlySearchLike = "'%" . $monthlySearchEscaped . "%'";
    if ($monthlyFilter === 'month') {
        $monthlyQuery = "WHERE visit_month LIKE $monthlySearchLike";
    } elseif ($monthlyFilter === 'year') {
        $monthlyQuery = "WHERE visit_year LIKE $monthlySearchLike";
    } else {
        $monthlyQuery = "WHERE visit_month LIKE $monthlySearchLike OR visit_year LIKE $monthlySearchLike";
    }
}

// Yearly Visits Filter
$yearlySearch = isset($_GET['yearly_search']) ? trim($_GET['yearly_search']) : '';
$yearlyQuery = "";
if ($yearlySearch !== '') {
    $yearlySearchEscaped = $conn->real_escape_string($yearlySearch);
    $yearlySearchLike = "'%" . $yearlySearchEscaped . "%'";
    $yearlyQuery = "WHERE visit_year LIKE $yearlySearchLike";
}

// Fetch Data with Pagination
$result = $conn->query("SELECT visit_date, visit_time, ip_address, count FROM visitors $dailyQuery ORDER BY visit_date DESC LIMIT $itemsPerPage OFFSET $offsetDaily");
if (!$result) {
    die("Query failed: " . $conn->error);
}
$daily_visits = [];
while ($row = $result->fetch_assoc()) {
    $daily_visits[] = $row;
}
$result->free();

$result = $conn->query("SELECT COUNT(*) AS total FROM visitors $dailyQuery");
if (!$result) {
    die("Query failed: " . $conn->error);
}
$row = $result->fetch_assoc();
$totalDaily = $row ? (int)$row['total'] : 0;
$result->free();
$totalDailyPages = ceil($totalDaily / $itemsPerPage);

$result = $conn->query("SELECT visit_month, visit_year, SUM(count) AS total_visits FROM visitors $monthlyQuery GROUP BY visit_year, visit_month ORDER BY visit_year DESC, visit_month DESC LIMIT $itemsPerPage OFFSET $offsetMonthly");
if (!$result) {
    die("Query failed: " . $conn->error);
}
$monthly_visits = [];
while ($row = $result->fetch_assoc()) {
    $monthly_visits[] = $row;
}
$result->free();

$result = $conn->query("SELECT COUNT(DISTINCT visit_year, visit_month) AS total FROM visitors $monthlyQuery");
if (!$result) {
    die("Query failed: " . $conn->error);
}
$row = $result->fetch_assoc();
$totalMonthly = $row ? (int)$row['total'] : 0;
$result->free();
$totalMonthlyPages = ceil($totalMonthly / $itemsPerPage);

$result = $conn->query("SELECT visit_year, SUM(count) AS total_visits FROM visitors $yearlyQuery GROUP BY visit_year ORDER BY visit_year DESC LIMIT $itemsPerPage OFFSET $offsetYearly");
if (!$result) {
    die("Query failed: " . $conn->error);
}
$yearly_visits = [];
while ($row = $result->fetch_assoc()) {
    $yearly_visits[] = $row;
}
$result->free();

$result = $conn->query("SELECT COUNT(DISTINCT visit_year) AS total FROM visitors $yearlyQuery");
if (!$result) {
    die("Query failed: " . $conn->error);
}
$row = $result->fetch_assoc();
$totalYearly = $row ? (int)$row['total'] : 0;
$result->free();
$totalYearlyPages = ceil($totalYearly / $itemsPerPage);

function fetch_single_value(mysqli $conn, string $sql, string $types = '', array $params = []): int
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $value = 0;
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row) {
            $value = (int)($row['total'] ?? 0);
        }
        $result->free();
    }
    $stmt->close();
    return $value;
}

$today = date('Y-m-d');
$currentMonth = date('m');
$currentYear = date('Y');

$todayVisits = fetch_single_value(
    $conn,
    "SELECT SUM(count) AS total FROM visitors WHERE visit_date = ?",
    "s",
    [$today]
);
$monthVisits = fetch_single_value(
    $conn,
    "SELECT SUM(count) AS total FROM visitors WHERE visit_month = ? AND visit_year = ?",
    "ss",
    [$currentMonth, $currentYear]
);
$yearVisits = fetch_single_value(
    $conn,
    "SELECT SUM(count) AS total FROM visitors WHERE visit_year = ?",
    "s",
    [$currentYear]
);
$allVisits = fetch_single_value(
    $conn,
    "SELECT SUM(count) AS total FROM visitors"
);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitors Statistics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;500;700&display=swap" rel="stylesheet">
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
        }
        #particles-js { display: none; }
        .container, .container-fluid {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 20px;
            margin: 20px auto;
            max-width: 1200px;
        }
        h1, h2, h3, h4, h5 {
            color: var(--primary-3);
            font-weight: 700;
        }
        .table {
            background: var(--surface);
            border-radius: 12px;
            border: 1px solid var(--border);
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
        }
        .table th {
            background: linear-gradient(135deg, #ede9fe, #e9d5ff);
            color: #4c1d95;
            font-weight: 700;
        }
        .table tbody tr:hover { background: #f5f3ff; }
        .form-control, .form-select {
            background: #fff;
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            height: 40px;
            font-size: 0.95rem;
            padding: 8px 12px;
            box-shadow: inset 0 1px 2px rgba(139, 92, 246, 0.08);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-2);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }
        .btn {
            border-radius: 999px;
            padding: 6px 14px;
            border: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            font-size: 0.9rem;
            gap: 6px;
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.18);
        }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-2)); color: #fff; }
        .btn-secondary { background: linear-gradient(135deg, #a78bfa, #8b5cf6); color: #fff; }
        .btn-outline-secondary { border: 1px solid var(--border); color: var(--primary-3); }
        .pagination .page-link {
            background: #ffffff;
            border: 1px solid var(--border);
            color: var(--primary-3);
            border-radius: 10px;
            font-weight: 600;
        }
        .pagination .page-link:hover,
        .pagination .page-item.active .page-link {
            background: var(--primary-2);
            color: #fff;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.4);
            border-radius: 10px;
            padding: 14px 16px;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 10px;
            padding: 14px 16px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .stat-card {
            background: linear-gradient(135deg, rgba(167, 139, 250, 0.12), rgba(255, 255, 255, 0.9));
            border: 1px solid rgba(139, 92, 246, 0.35);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 18px rgba(139, 92, 246, 0.12);
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(139, 92, 246, 0.15);
            color: var(--primary-3);
            font-size: 1.2rem;
        }
        .stat-label {
            color: var(--muted);
            font-weight: 600;
            font-size: 0.9rem;
        }
        .stat-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--primary-3);
        }
        @media (max-width: 992px) {
            .container, .container-fluid { margin: 16px; padding: 16px; }
            .dashboard-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 576px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }
            .btn-success { background: linear-gradient(135deg, #22c55e, #16a34a); color: #ffffff; }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); color: #ffffff; }
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
        .sidebar.active { left: 0; }
        .sidebar-toggle {
            position: fixed;
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
        .sidebar.active ~ .sidebar-toggle { left: 260px; }
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
        .popup-overlay {
            position: fixed;
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
        .popup-overlay.show { visibility: visible; opacity: 1; }
        .popup-box {
            background: #fff;
            border: 1px solid var(--border);
            padding: 20px;
            border-radius: 14px;
            width: 90%;
            max-width: 420px;
            box-shadow: var(--shadow);
        }
        .popup-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .popup-title { margin: 0; font-size: 1.15rem; font-weight: 700; color: var(--primary-3); }
        .popup-body { margin-bottom: 16px; color: var(--text); }
        .popup-footer { display: flex; justify-content: flex-end; gap: 8px; }
    </style>
</head>
<body>
    <div id="particles-js"></div>

    <!-- Welcome Message -->
    <!-- <?php if (isAdmin()): ?> -->
        <!-- <div class="alert alert-success alert-dismissible fade show" role="alert">
            ยินดีต้อนรับ ผู้ดูแลระบบ!'
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div> -->
    <!-- <?php elseif (isUser()): ?> -->
        <!-- <div class="alert alert-success alert-dismissible fade show" role="alert">
            ยินดีต้อนรับ ผู้ใช้ทั่วไป!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div> -->
    <!-- <?php endif; ?> -->

    <!-- Error Message (if any) -->
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Sidebar -->
    <div class="sidebar">
    <div class="sidebar-content">
        <a href="#" id="openPopup" class="sidebar-item"><i class="bi bi-person-circle"></i> เข้าสู่ระบบสำหรับเจ้าหน้าที่</a>
        <a href="UMS.php" class="sidebar-item"><i class="bi bi-people"></i> ระบบจัดการสมาชิก</a>
        <a href="stats.php" class="sidebar-item"><i class="bi bi-bar-chart"></i> สถิติผู้เยี่ยมชมเว็บไซต์</a>
        <a href="hidden_items.php" class="sidebar-item"><i class="bi bi-archive"></i> กู้คืนข้อมูล</a>
        <a href="https://dc.phsmun.go.th/" class="sidebar-item"><i class="bi bi-house"></i> หน้าหลัก</a>
    </div>
</div>
<div class="sidebar-toggle"><span>☰</span></div>

    <!-- Popup for Login Confirmation -->
    <div id="customPopup" class="popup-overlay">
    <div class="popup-box">
        <div class="popup-header">
            <h5 class="popup-title">ยืนยันการเข้าสู่ระบบ</h5>
            <button type="button" class="btn-close" id="closePopup" aria-label="Close"></button>
        </div>
        <div class="popup-body">คุณต้องการเข้าสู่ระบบสำหรับเจ้าหน้าที่หรือไม่?</div>
        <div class="popup-footer">
            <button type="button" class="btn btn-secondary" id="closePopupBtn">ยกเลิก</button>
            <a href="login.php" class="btn btn-success">ยืนยัน</a>
        </div>
    </div>
</div>
    </div>

    <div class="container">
        <h1 class="text-center my-4">สถิติผู้เข้าชมเว็บไซต์</h1>
        <div class="dashboard-grid">
            <div class="stat-card">
                <span class="stat-icon"><i class="bi bi-calendar-check"></i></span>
                <div>
                    <div class="stat-label">วันนี้</div>
                    <div class="stat-value"><?= number_format($todayVisits) ?></div>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon"><i class="bi bi-calendar3"></i></span>
                <div>
                    <div class="stat-label">เดือนนี้</div>
                    <div class="stat-value"><?= number_format($monthVisits) ?></div>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon"><i class="bi bi-calendar3-week"></i></span>
                <div>
                    <div class="stat-label">ปีนี้</div>
                    <div class="stat-value"><?= number_format($yearVisits) ?></div>
                </div>
            </div>
            <div class="stat-card">
                <span class="stat-icon"><i class="bi bi-bar-chart-line"></i></span>
                <div>
                    <div class="stat-label">ทั้งหมด</div>
                    <div class="stat-value"><?= number_format($allVisits) ?></div>
                </div>
            </div>
        </div>

        <!-- Daily Visits -->
        <h2 id="daily-section">ประจำวัน/ผู้เข้าชม</h2>
        <form class="d-flex flex-column flex-md-row align-items-stretch gap-2 mb-3" method="GET">
    <div class="input-group flex-column flex-md-row gap-2">
        <input type="text" name="yearly_search" class="form-control" placeholder="🔍 ค้นหา..." value="<?= htmlspecialchars($yearlySearch) ?>">
        <button type="submit" class="btn btn-primary flex-shrink-0"><i class="bi bi-search"></i> ค้นหา</button>
        <a href="?" class="btn btn-secondary flex-shrink-0"><i class="bi bi-arrow-clockwise"></i> รีเซ็ต</a>
    </div>
</form>
        <table class="table table-striped table-hover">
            <thead>
    <tr>
        <th colspan="4">
            <form class="d-flex align-items-center gap-2 justify-content-end" method="GET">
                <label for="items_per_page_daily" class="fw-semibold mb-0">แสดงต่อหน้า:</label>
                <select id="items_per_page_daily" name="items_per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                    <option value="5" <?= $itemsPerPage == 5 ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= $itemsPerPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= $itemsPerPage == 20 ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= $itemsPerPage == 50 ? 'selected' : '' ?>>50</option>
                </select>
                <input type="hidden" name="daily_search" value="<?= htmlspecialchars($dailySearch) ?>">
                <input type="hidden" name="daily_filter" value="<?= htmlspecialchars($dailyFilter) ?>">
            </form>
        </th>
    </tr>
    <tr>
        <th>วันที่</th>
        <th>เวลา</th>
        <th>IP Address</th>
        <th>ผู้เข้าชม</th>
    </tr>
            </thead>
            <tbody>
                <?php if (count($daily_visits) > 0): ?>
                    <?php foreach ($daily_visits as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['visit_date']) ?></td>
                            <td><?= htmlspecialchars($row['visit_time']) ?></td>
                            <td><?= htmlspecialchars($row['ip_address']) ?></td>
                            <td><?= htmlspecialchars($row['count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">ไม่มีข้อมูล</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ($totalDailyPages > 1): ?>
        <nav aria-label="Daily pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($pageDaily <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_daily' => 1]), 'daily-section') ?>">หน้าแรก</a>
                </li>
                <li class="page-item <?= ($pageDaily <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_daily' => max(1, $pageDaily - 1)]), 'daily-section') ?>">ก่อนหน้า</a>
                </li>
                <?php foreach (build_page_range($pageDaily, $totalDailyPages) as $page): ?>
                    <?php if ($page === '...'): ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    <?php else: ?>
                        <li class="page-item <?= ($page == $pageDaily) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_daily' => $page]), 'daily-section') ?>"><?= $page ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="page-item <?= ($pageDaily >= $totalDailyPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_daily' => min($totalDailyPages, $pageDaily + 1)]), 'daily-section') ?>">ถัดไป</a>
                </li>
                <li class="page-item <?= ($pageDaily >= $totalDailyPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_daily' => $totalDailyPages]), 'daily-section') ?>">สุดท้าย</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <p class="text-end">จำนวนข้อมูลทั้งหมด: <?= $totalDaily ?> รายการ</p>

        <!-- Monthly Visits -->
        <h2 id="monthly-section">รายเดือน/ผู้เข้าชม</h2>
        <form class="d-flex flex-column flex-md-row align-items-stretch gap-2 mb-3" method="GET">
    <div class="input-group flex-column flex-md-row gap-2">
        <input type="text" name="yearly_search" class="form-control" placeholder="🔍 ค้นหา..." value="<?= htmlspecialchars($yearlySearch) ?>">
        <button type="submit" class="btn btn-primary flex-shrink-0"><i class="bi bi-search"></i> ค้นหา</button>
        <a href="?" class="btn btn-secondary flex-shrink-0"><i class="bi bi-arrow-clockwise"></i> รีเซ็ต</a>
    </div>
</form>
        <table class="table table-striped table-hover">
            <thead>
    <tr>
        <th colspan="3">
            <form class="d-flex align-items-center gap-2 justify-content-end" method="GET">
                <label for="items_per_page_monthly" class="fw-semibold mb-0">แสดงต่อหน้า:</label>
                <select id="items_per_page_monthly" name="items_per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                    <option value="5" <?= $itemsPerPage == 5 ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= $itemsPerPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= $itemsPerPage == 20 ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= $itemsPerPage == 50 ? 'selected' : '' ?>>50</option>
                </select>
                <input type="hidden" name="monthly_search" value="<?= htmlspecialchars($monthlySearch) ?>">
                <input type="hidden" name="monthly_filter" value="<?= htmlspecialchars($monthlyFilter) ?>">
            </form>
        </th>
    </tr>
    <tr>
        <th>เดือน</th>
        <th>ปี</th>
        <th>จำนวนผู้เข้าชม</th>
    </tr>
            </thead>
            <tbody>
                <?php if (count($monthly_visits) > 0): ?>
                    <?php foreach ($monthly_visits as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['visit_month']) ?></td>
                            <td><?= htmlspecialchars($row['visit_year']) ?></td>
                            <td><?= htmlspecialchars($row['total_visits']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">ไม่มีข้อมูล</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ($totalMonthlyPages > 1): ?>
        <nav aria-label="Monthly pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($pageMonthly <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_monthly' => 1]), 'monthly-section') ?>">หน้าแรก</a>
                </li>
                <li class="page-item <?= ($pageMonthly <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_monthly' => max(1, $pageMonthly - 1)]), 'monthly-section') ?>">ก่อนหน้า</a>
                </li>
                <?php foreach (build_page_range($pageMonthly, $totalMonthlyPages) as $page): ?>
                    <?php if ($page === '...'): ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    <?php else: ?>
                        <li class="page-item <?= ($page == $pageMonthly) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_monthly' => $page]), 'monthly-section') ?>"><?= $page ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="page-item <?= ($pageMonthly >= $totalMonthlyPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_monthly' => min($totalMonthlyPages, $pageMonthly + 1)]), 'monthly-section') ?>">ถัดไป</a>
                </li>
                <li class="page-item <?= ($pageMonthly >= $totalMonthlyPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_monthly' => $totalMonthlyPages]), 'monthly-section') ?>">สุดท้าย</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <p class="text-end">จำนวนข้อมูลทั้งหมด: <?= $totalDaily ?> รายการ</p>

        <!-- Yearly Visits -->
        <h2 id="yearly-section">รายปี/ผู้เข้าชม</h2>
        <form class="d-flex flex-column flex-md-row align-items-stretch gap-2 mb-3" method="GET">
    <div class="input-group flex-column flex-md-row gap-2">
        <input type="text" name="yearly_search" class="form-control" placeholder="🔍 ค้นหา..." value="<?= htmlspecialchars($yearlySearch) ?>">
        <button type="submit" class="btn btn-primary flex-shrink-0"><i class="bi bi-search"></i> ค้นหา</button>
        <a href="?" class="btn btn-secondary flex-shrink-0"><i class="bi bi-arrow-clockwise"></i> รีเซ็ต</a>
    </div>
</form>
        <table class="table table-striped table-hover">
            <thead>
    <tr>
        <th colspan="2">
            <form class="d-flex align-items-center gap-2 justify-content-end" method="GET">
                <label for="items_per_page_yearly" class="fw-semibold mb-0">แสดงต่อหน้า:</label>
                <select id="items_per_page_yearly" name="items_per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                    <option value="5" <?= $itemsPerPage == 5 ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= $itemsPerPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= $itemsPerPage == 20 ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= $itemsPerPage == 50 ? 'selected' : '' ?>>50</option>
                </select>
                <input type="hidden" name="yearly_search" value="<?= htmlspecialchars($yearlySearch) ?>">
            </form>
        </th>
    </tr>
    <tr>
        <th>ปี</th>
        <th>จำนวนผู้เข้าชม</th>
    </tr>
            </thead>
            <tbody>
                <?php if (count($yearly_visits) > 0): ?>
                    <?php foreach ($yearly_visits as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['visit_year']) ?></td>
                            <td><?= htmlspecialchars($row['total_visits']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="text-center">ไม่มีข้อมูล</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ($totalYearlyPages > 1): ?>
        <nav aria-label="Yearly pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($pageYearly <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_yearly' => 1]), 'yearly-section') ?>">หน้าแรก</a>
                </li>
                <li class="page-item <?= ($pageYearly <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_yearly' => max(1, $pageYearly - 1)]), 'yearly-section') ?>">ก่อนหน้า</a>
                </li>
                <?php foreach (build_page_range($pageYearly, $totalYearlyPages) as $page): ?>
                    <?php if ($page === '...'): ?>
                        <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                    <?php else: ?>
                        <li class="page-item <?= ($page == $pageYearly) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_yearly' => $page]), 'yearly-section') ?>"><?= $page ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="page-item <?= ($pageYearly >= $totalYearlyPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_yearly' => min($totalYearlyPages, $pageYearly + 1)]), 'yearly-section') ?>">ถัดไป</a>
                </li>
                <li class="page-item <?= ($pageYearly >= $totalYearlyPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= build_stats_url(array_merge($_GET, ['page_yearly' => $totalYearlyPages]), 'yearly-section') ?>">สุดท้าย</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <p class="text-end">จำนวนข้อมูลทั้งหมด: <?= $totalDaily ?> รายการ</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        particlesJS("particles-js", {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: "#5e2a96" },
                shape: { type: "circle" },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: "#5e2a96", opacity: 0.4, width: 1 },
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
            // Table Animation
            const tables = document.querySelectorAll(".table tbody tr");
            tables.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add("visible");
                }, index * 150);
            });

            // Sidebar Toggle
            const sidebar = document.querySelector(".sidebar");
            const toggle = document.querySelector(".sidebar-toggle");
            if (sidebar && toggle) {
                toggle.addEventListener("click", function () {
                    sidebar.classList.toggle("active");
                });
            }

            // Popup Handling
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

            // Responsive Adjustments
            window.addEventListener("orientationchange", function () {
                const inputs = document.querySelectorAll(".form-control, .form-select");
                inputs.forEach(input => {
                    input.style.width = window.innerWidth < 576 ? "100%" : "auto";
                });
                if (sidebar) sidebar.classList.remove("active");
            });

            window.addEventListener("resize", function () {
                const tables = document.querySelectorAll(".table");
                tables.forEach(table => {
                    table.style.width = "100%";
                });
                if (sidebar && window.innerWidth > 992) sidebar.classList.remove("active");
            });

            // Close Sidebar and Popup on Escape Key
            document.addEventListener("keydown", function (event) {
                if (event.key === "Escape") {
                    if (sidebar) sidebar.classList.remove("active");
                    closePopupFunc();
                }
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







