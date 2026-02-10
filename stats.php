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
            max-width: 90%;
            padding: 20px;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid #28a745;
            margin-top: 63px;
            padding: 15px 40px 15px 15px;
            position: relative;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 15px 40px 15px 15px;
            position: relative;
        }
        .alert .btn-close {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%232d2d2d'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707A1 1 0 01.293.293z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            opacity: 1;
            width: 1.5em;
            height: 1.5em;
        }
        h1, h2 {
            color: #4b0082;
            font-weight: 700;
            font-size: 2.2rem;
            text-shadow: none;
        }
        h2 {
            font-size: 1.8rem;
            margin-top: 30px;
        }
        .table {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #d1c4e9;
            width: 100%;
        }
        .table th, .table td {
            border: 1px solid #d1c4e9;
            padding: 10px;
            color: #2d2d2d;
            vertical-align: middle;
            text-align: center;
            font-size: 0.9rem;
        }
        .table th {
            background: #f0e6ff;
            color: #4b0082;
            font-weight: 700;
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
            transform: scale(1.02);
        }
        .btn {
            border-radius: 10px;
            padding: 8px 16px;
            transition: all 0.3s ease;
            border: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        .btn-primary {
            background: #5e2a96;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #4b2078;
            box-shadow: 0 0 15px rgba(94, 42, 150, 0.5);
        }
        .btn-secondary {
            background: #6c757d;
            color: #ffffff;
        }
        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 0 10px rgba(108, 117, 125, 0.5);
        }
        .btn-success {
            background: #28a745;
            color: #ffffff;
        }
        .btn-success:hover {
            background: #218838;
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.5);
        }
        .form-control, .form-select {
            background: #ffffff;
            border: 1px solid #d1c4e9;
            color: #2d2d2d;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 500;
            height: 36px;
            font-size: 0.9rem;
            width: 100%;
            padding: 8px;
        }
        .form-control:focus, .form-select:focus {
            background: #ffffff;
            border-color: #5e2a96;
            box-shadow: 0 0 10px rgba(94, 42, 150, 0.5);
            color: #2d2d2d;
        }
        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
            align-items: stretch;
        }
        .pagination .page-link {
            background: #f0e6ff;
            border: none;
            color: #4b0082;
            margin: 0 5px;
            border-radius: 50%;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .pagination .page-link:hover {
            background: #5e2a96;
            color: #ffffff;
        }
        .pagination .page-item.active .page-link {
            background: #5e2a96;
            color: #ffffff;
        }
        .sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid #d1c4e9;
            position: fixed;
            top: 0;
            left: -260px;
            width: 260px;
            max-width: 80vw;
            height: 100%;
            transition: left 0.3s ease-in-out;
            z-index: 1000;
            display: block;
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
            color: #ffffff;
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
            margin-top: 70px;
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #4b0082;
            padding: 10px 12px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: background 0.2s, transform 0.2s;
            font-weight: 500;
        }
        .sidebar-item:hover {
            background: #f0e6ff;
            color: #5e2a96;
            transform: translateX(5px);
        }
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
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
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid #d1c4e9;
            padding: 15px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            min-width: 280px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        .popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #d1c4e9;
        }
        .popup-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #5e2a96;
        }
        .popup-box .btn-close {
            background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%235e2a96'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707A1 1 0 01.293.293z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #5e2a96;
        }
        .popup-body {
            margin-bottom: 20px;
            font-size: 16px;
            color: #2d2d2d;
            font-weight: 500;
        }
        .popup-footer {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        @media (max-width: 992px) {
            .container {
                padding: 15px;
            }
            .input-group {
                flex-direction: column;
                align-items: stretch;
            }
            .form-control, .form-select, .btn {
                width: 100%;
                max-width: none;
            }
            .btn {
                height: 34px;
                font-size: 0.85rem;
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
        }
        @media (max-width: 576px) {
            body {
                font-size: 0.9rem;
            }
            .container {
                padding: 10px;
                margin: 10px;
            }
            .form-control, .form-select {
                height: 32px;
                font-size: 0.8rem;
            }
            .btn {
                height: 32px;
                font-size: 0.8rem;
                padding: 6px 12px;
            }
            .table th, .table td {
                padding: 8px;
                font-size: 0.75rem;
            }
            .sidebar-toggle {
                width: 35px;
                height: 35px;
            }
        }
        @media (orientation: landscape) and (max-height: 500px) {
            .container {
                padding: 10px;
            }
            .input-group {
                flex-direction: row;
                flex-wrap: nowrap;
            }
            .form-control, .form-select {
                width: auto;
                flex-grow: 1;
            }
            .btn {
                font-size: 0.85rem;
            }
        }
    </style>
    <link href='css/styles.css' rel='stylesheet'>
</head>
<body>
    <div id="particles-js"></div>

    <!-- Welcome Message -->
    <!-- <?php if (isAdmin()): ?> -->
        <!-- <div class="alert alert-success alert-dismissible fade show" role="alert">
            ยินดีต้อนรับ ผู้ดูแลระบบ! 👑
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div> -->
    <!-- <?php elseif (isUser()): ?> -->
        <!-- <div class="alert alert-success alert-dismissible fade show" role="alert">
            ยินดีต้อนรับ ผู้ใช้ทั่วไป! 👤
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

    <div class="container">
        <h1 class="text-center my-4">สถิติผู้เข้าชมเว็บไซต์</h1>

        <!-- Daily Visits -->
        <h2 id="daily-section">ประจำวัน/ผู้เข้าชม</h2>
        <form class="d-flex flex-column flex-md-row align-items-stretch gap-2 mb-3" method="GET">
            <div class="input-group flex-column flex-md-row gap-2">
                <input type="text" name="daily_search" class="form-control" placeholder="🔍 ค้นหา..." value="<?= htmlspecialchars($dailySearch) ?>">
                <select name="daily_filter" class="form-select" style="width: 150px;">
                    <option value="all" <?= $dailyFilter === 'all' ? 'selected' : '' ?>>ทั้งหมด</option>
                    <option value="date" <?= $dailyFilter === 'date' ? 'selected' : '' ?>>วันที่</option>
                    <option value="ip" <?= $dailyFilter === 'ip' ? 'selected' : '' ?>>IP Address</option>
                </select>
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
                <input type="text" name="monthly_search" class="form-control" placeholder="🔍 ค้นหา..." value="<?= htmlspecialchars($monthlySearch) ?>">
                <select name="monthly_filter" class="form-select" style="width: 150px;">
                    <option value="all" <?= $monthlyFilter === 'all' ? 'selected' : '' ?>>ทั้งหมด</option>
                    <option value="month" <?= $monthlyFilter === 'month' ? 'selected' : '' ?>>เดือน</option>
                    <option value="year" <?= $monthlyFilter === 'year' ? 'selected' : '' ?>>ปี</option>
                </select>
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
        <p class="text-end">จำนวนข้อมูลทั้งหมด: <?= $totalMonthly ?> รายการ</p>

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
        <p class="text-end">จำนวนข้อมูลทั้งหมด: <?= $totalYearly ?> รายการ</p>
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

