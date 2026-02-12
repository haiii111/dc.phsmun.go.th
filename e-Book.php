<?php
session_start();
// include 'db.php';
include 'auth.php';

$roleBanner = "";
if (isAdmin()) {
    $roleBanner = '<div class="role-banner role-admin"><i class="fas fa-crown"></i>ยินดีต้อนรับ ผู้ดูแลระบบ</div>';
} elseif (isUser()) {
    $roleBanner = '<div class="role-banner role-user"><i class="fas fa-user"></i>ยินดีต้อนรับ ผู้ใช้ทั่วไป</div>';
}
$itemsPerPage = isset($_GET['items_per_page']) ? (int)$_GET['items_per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchField = isset($_GET['search_field']) ? trim($_GET['search_field']) : 'all';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
$searchQuery = "WHERE hidden = 0";

if ($search !== '') {
    $searchEscaped = mysqli_real_escape_string($conn, $search);
    if ($searchField === 'name') {
        $searchQuery .= " AND name LIKE '%$searchEscaped%'";
    } elseif ($searchField === 'details') {
        $searchQuery .= " AND details LIKE '%$searchEscaped%'";
    } else {
        $searchQuery .= " AND (name LIKE '%$searchEscaped%' OR details LIKE '%$searchEscaped%')";
    }
}

if ($filter !== '' && $filter !== 'all') {
    list($filterField, $filterType) = explode(':', $filter, 2);
    $filterTypeEscaped = mysqli_real_escape_string($conn, $filterType);
    if ($filterField === 'name') {
        $searchQuery .= " AND name LIKE '%$filterTypeEscaped%'";
    } elseif ($filterField === 'details') {
        $searchQuery .= " AND details LIKE '%$filterTypeEscaped%'";
    } else {
        $searchQuery .= " AND (name LIKE '%$filterTypeEscaped%' OR details LIKE '%$filterTypeEscaped%')";
    }
}

$sql = "SELECT * FROM items $searchQuery ORDER BY created_at DESC LIMIT $itemsPerPage OFFSET $offset";
$result = $conn->query($sql);

$totalItemsQuery = "SELECT COUNT(*) AS total FROM items $searchQuery";
$totalItemsResult = $conn->query($totalItemsQuery);
$totalItems = $totalItemsResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
        .alert-success {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.4);
            border-radius: 10px;
            padding: 14px 16px;
            position: relative;
            margin-top: 63px;
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
            width: 1.5em;
            height: 1.5em;
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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
                .table td:nth-child(2) {
            text-align: left;
        }
        .table td:nth-child(3) {
            text-align: center;
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
        .btn-sm {
            padding: 4px 10px;
            font-size: 0.82rem;
            height: 32px;
            max-width: 140px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: #ffffff;
        }
        .btn-primary:hover {
            box-shadow: 0 10px 18px rgba(139, 92, 246, 0.28);
            transform: translateY(-1px);
        }
        .btn-success {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #ffffff;
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
        }
        .btn-secondary {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            color: #ffffff;
        }
        .btn-warning {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #1f2937;
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
        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
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
        .modal {
            display: none;
            position: absolute;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.85);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-content {
            max-width: 80vw;
            max-height: 80vh;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        .close {
            position: absolute;
            top: 10px;
            right: 0;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .image-thumbnail {
            cursor: pointer;
            transition: transform 0.2s ease;
            border-radius: 8px;
        }
        .image-thumbnail:hover {
            transform: scale(1.05);
        }
        @media (max-width: 992px) {
            .container-fluid { padding: 16px; margin: 16px; }
            .input-group { flex-direction: column; align-items: stretch; }
            .form-control, .form-select, .btn { width: 100%; max-width: 100%; }
            h1 { font-size: 2rem; }
        }
        @media (max-width: 576px) {
            body { font-size: 0.92rem; }
            .container-fluid { padding: 14px; margin: 12px; }
            .btn { height: 34px; font-size: 0.82rem; }
            .btn-sm { height: 30px; font-size: 0.74rem; }
            .table th, .table td { font-size: 0.85rem; padding: 8px; }
            .table-toolbar { flex-direction: column; align-items: stretch; }
            .table-toolbar label { width: 100%; }
            .table-toolbar .form-select { width: 100%; max-width: 100%; }
            .action-buttons { width: 100%; }
            .action-buttons .btn { width: 100%; max-width: 100%; }
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
        .role-user {
            background: linear-gradient(135deg, #e0f2fe, #f0f9ff);
            border-color: rgba(14, 165, 233, 0.25);
            color: #0f172a;
        }
        @media (max-width: 768px) {
            .role-banner {
                right: 0;
                right: auto;
                
            }
        }
        .form-card {
            background: var(--surface-2);
            border: 2px solid rgba(139, 92, 246, 0.45);
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 6px 16px rgba(139, 92, 246, 0.12);
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
        .badge-new {
            background: linear-gradient(135deg, #f97316, #ef4444);
            color: #fff;
            font-size: 0.72rem;
            padding: 4px 8px;
            border-radius: 999px;
            letter-spacing: 0.3px;
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
        }
        .badge-new::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -100%;
            width: 50%;
            height: 200%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.7), transparent);
            transform: skewX(-20deg);
            animation: badge-shine 2.2s ease-in-out infinite;
        }
        @keyframes badge-shine {
            0% { left: -120%; }
            60% { left: 120%; }
            100% { left: 120%; }
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <?= $roleBanner ?>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isGuest() || isUser() || isAdmin()): ?>
    <div class="sidebar">
        <div class="sidebar-content"> 
            <a href="#" id="openPopup" class="sidebar-item"><i class="fas fa-user-lock"></i> เข้าสู่ระบบสำหรับเจ้าหน้าที่</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="UMS.php" class="sidebar-item"><i class="fas fa-users"></i> ระบบจัดการสมาชิก</a>
            <a href="stats.php" class="sidebar-item"><i class="fas fa-chart-line"></i> สถิติผู้เยี่ยมชมเว็บไซต์</a>
            <a href="login_logs.php" class="sidebar-item"><i class="fas fa-sign-in-alt"></i> บันทึกการลงชื่อเข้าใช้</a>
            <a href="hidden_items.php" class="sidebar-item"><i class="bi bi-archive"></i> กู้คืนข้อมูล</a>
            <?php endif; ?>
            <a href="https://dc.phsmun.go.th/" class="sidebar-item"><i class="fas fa-home"></i> หน้าหลัก</a>
        </div>
    </div>
    <div class="sidebar-toggle"><i class="fas fa-bars"></i></div>

    <div id="customPopup" class="popup-overlay">
        <div class="popup-box">
            <div class="popup-header">
                <h5 class="popup-title"><i class="fas fa-sign-in-alt me-2"></i>ยินยันการเข้าสู่ระบบ</h5>
                <button type="button" class="btn-close" id="closePopup" aria-label="Close"></button>
            </div>
            <div class="popup-body">คุณต้องการเข้าสู่ระบบสำหรับเจ้าหน้าที่หรือไม่?</div>
            <div class="popup-footer d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-secondary" id="closePopupBtn"><i class="fas fa-times me-2"></i>ยกเลิก</button>
                <a href="login.php" class="btn btn-success"><i class="fas fa-check me-2"></i>ยืนยัน</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="container-fluid mt-5">
        <h1><i class="fas fa-book-open me-2"></i>ชั้นหนังสืออิเล็กทรอนิกส์</h1>
        <div class="form-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <form class="d-flex flex-column flex-md-row gap-2 w-100" method="GET" action="e-Book.php">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(session_id().bin2hex(random_bytes(8))) ?>">
                <div class="input-group flex-column flex-md-row gap-2">
                    <input type="text" name="search" class="form-control" placeholder="🔍 ค้นหา..." value="<?= htmlspecialchars($search??'') ?>" maxlength="100" pattern="[A-Za-z0-9ก-๙\s]*">
                    <select name="search_field" class="form-select">
                        <option value="all" <?=($searchField??'all')=='all'?'selected':''?>>ทั้งหมด</option>
                        <option value="name" <?=($searchField??'')=='name'?'selected':''?>>ชื่อ</option>
                        <option value="details" <?=($searchField??'')=='details'?'selected':''?>>รายละเอียด</option>
                    </select>
                    <select name="filter" class="form-select">
                        <option value="all" <?=($filter??'')==''||($filter??'')=='all'?'selected':''?>>ทุกประเภท</option>
                        <optgroup label="ชื่อ">
                            <?php foreach(['คู่มือ','เทศบัญญัติ','รายงาน','รายงานการประชุมสภา','แผนการบริหาร','แผนการการดำเนินงาน','ประกาศ'] as $f): ?>
                                <option value="name:<?=$f?>" <?=($filter??'')=="name:$f"?'selected':''?>><?=$f?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="รายละเอียด">
                            <?php foreach(['2567','2568','2569'] as $y): ?>
                                <option value="details:<?=$y?>" <?=($filter??'')=="details:$y"?'selected':''?>>ปี<?=$y?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="ทั้งหมด">
                            <?php foreach(['คู่มือ','เทศบัญญัติ','รายงาน','รายงานการประชุมสภา','แผนการบริหาร','แผนการการดำเนินงาน','ประกาศ'] as $f): ?>
                                <option value="all:<?=$f?>" <?=($filter??'')=="all:$f"?'selected':''?>><?=$f?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> ค้นหา</button>
                    <a href="e-Book.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> รีเซ็ต</a>
                </div>
            </form>
            <div class="d-flex flex-column flex-sm-row gap-2 action-buttons">
                <?php if(isAdmin()||isUser()): ?><a href="add.php" class="btn btn-success"><i class="fas fa-plus"></i> เพิ่มข้อมูล</a><?php endif; ?>
                <?php if(!isGuest()): ?><a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a><?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th colspan="<?= !isGuest() ? 6 : 5 ?>">
                        <form class="d-flex align-items-center gap-2 justify-content-end table-toolbar" method="GET">
                            <label for="items_per_page" class="fw-semibold mb-0">แสดงข้อมูลต่อหน้า:</label>
                            <select id="items_per_page" name="items_per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                <option value="5" <?= $itemsPerPage == 5 ? 'selected' : '' ?>>5</option>
                                <option value="10" <?= $itemsPerPage == 10 ? 'selected' : '' ?>>10</option>
                                <option value="20" <?= $itemsPerPage == 20 ? 'selected' : '' ?>>20</option>
                                <option value="50" <?= $itemsPerPage == 50 ? 'selected' : '' ?>>50</option>
                            </select>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                            <input type="hidden" name="search_field" value="<?= htmlspecialchars($searchField) ?>">
                            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        </form>
                    </th>
                </tr>
                <tr>
                    <th style="width: 5%;">ลำดับ</th>
                    <th style="width: 35%;">ชื่อ</th>
                    <th style="width: 25%;">รายละเอียด</th>
                    <th style="width: 10%;">รูปภาพ</th>
                    <th style="width: 15%;">ไฟล์ PDF</th>
                    <?php if (!isGuest()): ?>
                    <th style="width: 20%;">การจัดการ</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                <?php foreach ($result as $index => $item): ?>
                <?php
                    $createdAt = $item['created_at'] ?? null;
                    $isNew = $createdAt && (strtotime($createdAt) >= (time() - 2 * 24 * 60 * 60));
                ?>
                <tr>
                    <td><?= $offset + $index + 1 ?></td>
                    <td>
                        <span><?= htmlspecialchars($item['name']) ?></span>
                        <?php if ($isNew): ?>
                            <span class="badge badge-new ms-2">ใหม่ล่าสุด</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['details']) ?></td>
                    <td>
                        <?php if ($item['image']): ?>
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Image" width="50" class="image-thumbnail" onclick="openModal('uploads/<?= htmlspecialchars($item['image']) ?>')">
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($item['pdf_file']): ?>
                        <a href="uploads/<?= htmlspecialchars($item['pdf_file']) ?>" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> เปิดอ่าน</a>
                        <a href="uploads/<?= htmlspecialchars($item['pdf_file']) ?>" download class="btn btn-sm btn-secondary"><i class="fas fa-download"></i> ดาวน์โหลด</a>
                        <?php endif; ?>
                    </td>
                    <?php if (!isGuest()): ?>
                    <td>
                        <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> แก้ไข</a>
                        <a href="delete.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณต้องการลบข้อมูลนี้หรือไม่?')"><i class="fas fa-trash"></i> ลบ</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="<?= !isGuest() ? 6 : 5 ?>" class="text-center">ไม่มีข้อมูล</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= max(1, $page - 1) ?>&search=<?= htmlspecialchars($search) ?>&search_field=<?= htmlspecialchars($searchField) ?>&filter=<?= htmlspecialchars($filter) ?>&items_per_page=<?= $itemsPerPage ?>"><i class="fas fa-chevron-left"></i></a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>&search_field=<?= htmlspecialchars($searchField) ?>&filter=<?= htmlspecialchars($filter) ?>&items_per_page=<?= $itemsPerPage ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= htmlspecialchars($search) ?>&search_field=<?= htmlspecialchars($searchField) ?>&filter=<?= htmlspecialchars($filter) ?>&items_per_page=<?= $itemsPerPage ?>"><i class="fas fa-chevron-right"></i></a>
                </li>
            </ul>
        </nav>

        <p class="text-end">จำนวนข้อมูลทั้งหมด: <?= $totalItems ?> รายการ</p>
    </div>

    <div id="imageModal" class="modal">
        <span class="close">×</span>
        <img class="modal-content" id="fullImage">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
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
            const rows = document.querySelectorAll(".table tbody tr");
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add("visible");
                }, index * 100);
            });

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

            const modal = document.getElementById("imageModal");
            const fullImage = document.getElementById("fullImage");
            const closeButton = document.querySelector(".close");

            function openModal(src) {
                if (!src) return;
                fullImage.src = src;
                modal.style.display = "flex";
                fullImage.onload = function () {
                    if (fullImage.naturalWidth > fullImage.naturalHeight) {
                        fullImage.style.width = "80vw";
                        fullImage.style.height = "auto";
                    } else {
                        fullImage.style.width = "auto";
                        fullImage.style.height = "80vh";
                    }
                };
            }

            function closeModal() {
                modal.style.display = "none";
            }

            closeButton.addEventListener("click", closeModal);
            modal.addEventListener("click", function (event) {
                if (event.target === modal) closeModal();
            });
            document.addEventListener("keydown", function (event) {
                if (event.key === "Escape") {
                    if (sidebar) sidebar.classList.remove("active");
                    closePopupFunc();
                    closeModal();
                }
            });
            window.openModal = openModal;

            window.addEventListener("orientationchange", function () {
                const inputs = document.querySelectorAll(".form-control, .form-select");
                inputs.forEach(input => {
                    input.style.width = window.innerWidth < 576 ? "100%" : "auto";
                });
                if (sidebar) sidebar.classList.remove("active");
            });

            window.addEventListener("resize", function () {
                const table = document.querySelector(".table");
                if (table) table.style.width = "100%";
                if (sidebar && window.innerWidth > 992) sidebar.classList.remove("active");
            });
        });
    </script>
    <script>
        setTimeout(function () {
            var banner = document.querySelector('.role-banner');
            if (banner) {
                banner.style.transition = 'opacity 0.3s ease';
                banner.style.opacity = '0';
                banner.style.pointerEvents = 'none';
            }
        }, 10000);
        // roleBannerTimer
    </script>
</body>
</html>





