<?php
session_start();
include 'db.php';
include 'auth.php';

if (isAdmin()) {
    echo "ยินดีต้อนรับ ผู้ดูแลระบบ!👑";
} elseif (isUser()) {
    echo "ยินดีต้อนรับ บุคคลทั่วไป!👤";
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
        .alert-success {
            background: rgba(0, 255, 136, 0.1);
            color: #28a745;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            position: relative;
            margin-top: 63px;
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
        .btn-primary {
            background: #5e2a96;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #4b2078;
            box-shadow: 0 0 10px rgba(94, 42, 150, 0.5);
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
            background: #c62828;
            color: #ffffff;
        }
        .btn-danger:hover {
            background: #a32121;
            box-shadow: 0 0 10px rgba(198, 40, 40, 0.5);
        }
        .btn-secondary {
            background: #6c757d;
            color: #ffffff;
        }
        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 0 10px rgba(108, 117, 125, 0.5);
        }
        .btn-warning {
            background: #ffb300;
            color: #2d2d2d;
        }
        .btn-warning:hover {
            background: #e0a800;
            box-shadow: 0 0 10px rgba(255, 179, 0, 0.5);
        }
        .form-control, .form-select {
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
        .form-control:focus, .form-select:focus {
            border-color: #5e2a96;
            box-shadow: 0 0 8px rgba(94, 42, 150, 0.3);
            background: #fff;
        }
        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
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
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
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
            border: 1px solid #d1c4e9;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .popup-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #4b0082;
        }
        .popup-box .btn-close {
            background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%235e2a96'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707A1 1 0 01.293.293z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            color: #5e2a96;
            opacity: 1;
            font-size: 1.1rem;
            padding: 0.5rem;
            width: 1.5em;
            height: 1.5em;
            cursor: pointer;
        }
        .popup-body {
            margin-bottom: 20px;
            font-size: 1rem;
            color: #2d2d2d;
        }
        .popup-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            max-width: 80vw;
            max-height: 80vh;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .image-thumbnail {
            cursor: pointer;
            transition: transform 0.3s;
            border-radius: 8px;
        }
        .image-thumbnail:hover {
            transform: scale(1.1);
        }
        @media (max-width: 992px) {
            .container-fluid {
                padding: 12px;
                margin: 10px;
            }
            .input-group {
                flex-direction: column;
                align-items: stretch;
            }
            .form-control, .form-select, .btn {
                width: 100%;
                max-width: 100%;
            }
            .btn {
                height: 34px;
                font-size: 0.85rem;
                max-width: 100%;
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
            .form-control, .form-select {
                height: 34px;
                font-size: 0.85rem;
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
            .popup-box {
                padding: 15px;
                max-width: 320px;
            }
            .sidebar-toggle {
                width: 36px;
                height: 36px;
            }
            .d-flex.gap-2 {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
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
    <div id="particles-js"></div>

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
            <div class="popup-footer">
                <button type="button" class="btn btn-secondary" id="closePopupBtn"><i class="fas fa-times me-2"></i>ยกเลิก</button>
                <a href="login.php" class="btn btn-success"><i class="fas fa-check me-2"></i>ยืนยัน</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="container-fluid mt-5">
        <h1><i class="fas fa-book-open me-2"></i>ชั้นหนังสืออิเล็กทรอนิกส์</h1>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
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
                            <?php foreach(['2567','2568'] as $y): ?>
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
            <div class="d-flex gap-2">
                <?php if(isAdmin()||isUser()): ?><a href="add.php" class="btn btn-success"><i class="fas fa-plus"></i> เพิ่มข้อมูล</a><?php endif; ?>
                <?php if(!isGuest()): ?><a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a><?php endif; ?>
            </div>
        </div>

        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th colspan="<?= !isGuest() ? 6 : 5 ?>">
                        <form class="d-flex align-items-center gap-2 justify-content-end" method="GET">
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
                <tr>
                    <td><?= $offset + $index + 1 ?></td>
                    <td><?= htmlspecialchars($item['name']) ?></td>
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
</body>
</html>