<?php
session_start();
// include 'db.php';
include 'auth.php';

if (!function_exists('ebook_text_length')) {
    function ebook_text_length(string $text): int
    {
        return function_exists('mb_strlen') ? (int) mb_strlen($text, 'UTF-8') : strlen($text);
    }
}

if (!function_exists('ebook_format_date')) {
    function ebook_format_date(?string $value, bool $includeTime = false): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '-';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return $value;
        }

        return $includeTime ? date('d/m/Y H:i', $timestamp) : date('d/m/Y', $timestamp);
    }
}

if (!function_exists('ebook_highlight_html')) {
    function ebook_highlight_html(string $text, string $query, bool $preserveLineBreaks = false): string
    {
        $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $query = trim($query);

        if ($query !== '') {
            $terms = preg_split('/\s+/u', $query) ?: [];
            $terms = array_values(array_unique(array_filter($terms, static fn ($term): bool => $term !== '')));
            usort($terms, static fn ($left, $right): int => strlen($right) <=> strlen($left));

            if ($terms !== []) {
                $escapedTerms = array_map(
                    static fn ($term): string => preg_quote(htmlspecialchars($term, ENT_QUOTES, 'UTF-8'), '/'),
                    $terms
                );
                $pattern = '/(' . implode('|', $escapedTerms) . ')/iu';
                $escapedText = preg_replace($pattern, '<mark>$1</mark>', $escapedText) ?? $escapedText;
            }
        }

        return $preserveLineBreaks ? nl2br($escapedText) : $escapedText;
    }
}

$roleBanner = "";
if (isAdmin()) {
    $roleBanner = '<div class="role-banner role-admin"><i class="bi bi-shield-lock"></i>ยินดีต้อนรับ ผู้ดูแลระบบ</div>';
} elseif (isUser()) {
    $roleBanner = '<div class="role-banner role-user"><i class="bi bi-person-circle"></i>ยินดีต้อนรับ ผู้ใช้ทั่วไป</div>';
}
$ebookFilterKeys = ['search', 'search_field', 'category_id', 'subcategory_id', 'fiscal_year', 'sort', 'items_per_page'];
$defaultFilterState = [
    'search' => '',
    'search_field' => 'all',
    'category_id' => 0,
    'subcategory_id' => 0,
    'fiscal_year' => 0,
    'sort' => 'latest',
    'items_per_page' => 50,
];
$hasIncomingFilterState = false;
foreach ($ebookFilterKeys as $filterKey) {
    if (array_key_exists($filterKey, $_GET)) {
        $hasIncomingFilterState = true;
        break;
    }
}

$requestedFilterState = $defaultFilterState;
if (!$hasIncomingFilterState && isset($_SESSION['ebook_filter_state']) && is_array($_SESSION['ebook_filter_state'])) {
    $requestedFilterState = array_merge($requestedFilterState, $_SESSION['ebook_filter_state']);
}
foreach ($ebookFilterKeys as $filterKey) {
    if (array_key_exists($filterKey, $_GET)) {
        $requestedFilterState[$filterKey] = $_GET[$filterKey];
    }
}

$allowedPageSizes = [30, 50, 70, 100];
$itemsPerPage = (int) ($requestedFilterState['items_per_page'] ?? 50);
if (!in_array($itemsPerPage, $allowedPageSizes, true)) {
    $itemsPerPage = 50;
}

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $itemsPerPage;

$search = trim((string) ($requestedFilterState['search'] ?? ''));
$searchField = trim((string) ($requestedFilterState['search_field'] ?? 'all'));
$filter = '';
if (!in_array($searchField, ['all', 'name', 'details', 'category'], true)) {
    $searchField = 'all';
}

$sort = trim((string) ($requestedFilterState['sort'] ?? 'latest'));
if (!in_array($sort, ['latest', 'year', 'name'], true)) {
    $sort = 'latest';
}

$categoryId = (int) ($requestedFilterState['category_id'] ?? 0);
$subcategoryId = (int) ($requestedFilterState['subcategory_id'] ?? 0);
$fiscalYear = isset($requestedFilterState['fiscal_year']) && preg_match('/^\d{4}$/', (string) $requestedFilterState['fiscal_year'])
    ? (int) $requestedFilterState['fiscal_year']
    : 0;

$categories = get_document_categories($conn);
$categoryMap = get_document_category_map($conn);
$subcategoryMap = get_document_subcategory_map($conn);
$subcategoryGroups = get_document_subcategories_grouped($conn);
$fiscalYears = get_document_fiscal_years($conn);

if (!isset($categoryMap[$categoryId])) {
    $categoryId = 0;
}
if (!isset($subcategoryMap[$subcategoryId])) {
    $subcategoryId = 0;
}
if ($subcategoryId > 0) {
    $subcategoryCategoryId = (int) ($subcategoryMap[$subcategoryId]['category_id'] ?? 0);
    if ($categoryId === 0) {
        $categoryId = $subcategoryCategoryId;
    }
    if ($subcategoryCategoryId !== $categoryId) {
        $subcategoryId = 0;
    }
}

$selectedCategory = $categoryId > 0 && isset($categoryMap[$categoryId]) ? $categoryMap[$categoryId] : null;
$selectedCategorySubcategories = $selectedCategory !== null ? ($subcategoryGroups[(int) $selectedCategory['id']] ?? []) : [];
$selectedSubcategory = $subcategoryId > 0 && isset($subcategoryMap[$subcategoryId]) ? $subcategoryMap[$subcategoryId] : null;

if ($selectedCategory !== null && $selectedCategorySubcategories !== []) {
    $allowedSubcategoryIds = array_map(static fn (array $row): int => (int) $row['id'], $selectedCategorySubcategories);
    if ($subcategoryId > 0 && !in_array($subcategoryId, $allowedSubcategoryIds, true)) {
        $subcategoryId = 0;
        $selectedSubcategory = null;
    }
}

$_SESSION['ebook_filter_state'] = [
    'search' => $search,
    'search_field' => $searchField,
    'category_id' => $categoryId,
    'subcategory_id' => $subcategoryId,
    'fiscal_year' => $fiscalYear,
    'sort' => $sort,
    'items_per_page' => $itemsPerPage,
];

$sharedWhere = ['items.hidden = 0'];
$sharedTypes = '';
$sharedParams = [];

if ($search !== '') {
    $keyword = '%' . $search . '%';
    if ($searchField === 'name') {
        $sharedWhere[] = 'items.name LIKE ?';
        $sharedTypes .= 's';
        $sharedParams[] = $keyword;
    } elseif ($searchField === 'details') {
        $sharedWhere[] = 'items.details LIKE ?';
        $sharedTypes .= 's';
        $sharedParams[] = $keyword;
    } elseif ($searchField === 'category') {
        $sharedWhere[] = '(dc.reference_label LIKE ? OR dc.title LIKE ? OR ds.label LIKE ?)';
        $sharedTypes .= 'sss';
        $sharedParams[] = $keyword;
        $sharedParams[] = $keyword;
        $sharedParams[] = $keyword;
    } else {
        $sharedWhere[] = '(items.name LIKE ? OR items.details LIKE ? OR dc.reference_label LIKE ? OR dc.title LIKE ? OR ds.label LIKE ? OR ds.description LIKE ?)';
        $sharedTypes .= 'ssssss';
        $sharedParams[] = $keyword;
        $sharedParams[] = $keyword;
        $sharedParams[] = $keyword;
        $sharedParams[] = $keyword;
        $sharedParams[] = $keyword;
        $sharedParams[] = $keyword;
    }
}

if ($fiscalYear > 0) {
    $sharedWhere[] = 'items.fiscal_year = ?';
    $sharedTypes .= 'i';
    $sharedParams[] = $fiscalYear;
}

$activeWhere = $sharedWhere;
$activeTypes = $sharedTypes;
$activeParams = $sharedParams;

if ($selectedCategory !== null) {
    $activeWhere[] = 'items.category_id = ?';
    $activeTypes .= 'i';
    $activeParams[] = $categoryId;
}

if ($selectedSubcategory !== null) {
    $activeWhere[] = 'items.subcategory_id = ?';
    $activeTypes .= 'i';
    $activeParams[] = $subcategoryId;
}

$fromClause = ' FROM items LEFT JOIN document_categories dc ON dc.id = items.category_id LEFT JOIN document_subcategories ds ON ds.id = items.subcategory_id ';
$sharedWhereClause = ' WHERE ' . implode(' AND ', $sharedWhere);
$whereClause = ' WHERE ' . implode(' AND ', $activeWhere);
$sortOrderMap = [
    'latest' => ' ORDER BY COALESCE(dc.sort_order, 999), COALESCE(items.document_date, DATE(items.created_at)) DESC, items.created_at DESC ',
    'year' => ' ORDER BY COALESCE(items.fiscal_year, 0) DESC, COALESCE(items.document_date, DATE(items.created_at)) DESC, items.created_at DESC ',
    'name' => ' ORDER BY items.name ASC, COALESCE(items.document_date, DATE(items.created_at)) DESC, items.created_at DESC ',
];
$orderClause = $sortOrderMap[$sort];

$sql = 'SELECT items.*, dc.code AS category_code, dc.reference_label AS category_reference, dc.title AS category_title, dc.description AS category_description, ds.label AS subcategory_label, ds.description AS subcategory_description'
    . $fromClause
    . $whereClause
    . $orderClause
    . ' LIMIT ? OFFSET ?';

$resultStmt = $conn->prepare($sql);
$resultParams = array_merge($activeParams, [$itemsPerPage, $offset]);
bind_dynamic_params($resultStmt, $activeTypes . 'ii', $resultParams);
$resultStmt->execute();
$result = $resultStmt->get_result();
$items = $result instanceof mysqli_result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$totalItemsQuery = 'SELECT COUNT(*) AS total' . $fromClause . $whereClause;
$totalStmt = $conn->prepare($totalItemsQuery);
bind_dynamic_params($totalStmt, $activeTypes, $activeParams);
$totalStmt->execute();
$totalItemsResult = $totalStmt->get_result();
$totalItems = (int) ($totalItemsResult->fetch_assoc()['total'] ?? 0);
$totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
$visibleFrom = $totalItems > 0 ? ($offset + 1) : 0;
$visibleTo = min($totalItems, $offset + count($items));

$baseCountQuery = 'SELECT COUNT(*) AS total' . $fromClause . $sharedWhereClause;
$baseCountStmt = $conn->prepare($baseCountQuery);
bind_dynamic_params($baseCountStmt, $sharedTypes, $sharedParams);
$baseCountStmt->execute();
$baseCountResult = $baseCountStmt->get_result();
$baseTotalItems = (int) ($baseCountResult->fetch_assoc()['total'] ?? 0);

$categoryCounts = [];
foreach ($categories as $categoryRow) {
    $categoryCounts[(int) $categoryRow['id']] = 0;
}
$categoryCountQuery = 'SELECT items.category_id, COUNT(*) AS total' . $fromClause . $sharedWhereClause . ' GROUP BY items.category_id';
$categoryCountStmt = $conn->prepare($categoryCountQuery);
bind_dynamic_params($categoryCountStmt, $sharedTypes, $sharedParams);
$categoryCountStmt->execute();
$categoryCountResult = $categoryCountStmt->get_result();
if ($categoryCountResult instanceof mysqli_result) {
    while ($countRow = $categoryCountResult->fetch_assoc()) {
        $countCategoryId = (int) ($countRow['category_id'] ?? 0);
        if ($countCategoryId > 0) {
            $categoryCounts[$countCategoryId] = (int) ($countRow['total'] ?? 0);
        }
    }
}

$articleFilterCounts = ['all' => $baseTotalItems];
$articleFilterCategoryOrder = [];
$articleFilterTitles = [];
foreach ($categories as $categoryRow) {
    $articleFilterCategoryOrder[] = (int) $categoryRow['id'];
    $articleFilterCounts[(string) $categoryRow['id']] = (int) ($categoryCounts[(int) $categoryRow['id']] ?? 0);
    $articleFilterTitles[(string) $categoryRow['id']] = trim((string) ($categoryRow['title'] ?? ''));
}

$subcategoryCounts = [];
foreach ($subcategoryGroups as $subcategoryRows) {
    foreach ($subcategoryRows as $subcategoryRow) {
        $subcategoryCounts[(int) $subcategoryRow['id']] = 0;
    }
}
$subcategoryCountQuery = 'SELECT items.subcategory_id, COUNT(*) AS total' . $fromClause . $sharedWhereClause . ' GROUP BY items.subcategory_id';
$subcategoryCountStmt = $conn->prepare($subcategoryCountQuery);
bind_dynamic_params($subcategoryCountStmt, $sharedTypes, $sharedParams);
$subcategoryCountStmt->execute();
$subcategoryCountResult = $subcategoryCountStmt->get_result();
if ($subcategoryCountResult instanceof mysqli_result) {
    while ($subcategoryCountRow = $subcategoryCountResult->fetch_assoc()) {
        $countSubcategoryId = (int) ($subcategoryCountRow['subcategory_id'] ?? 0);
        if ($countSubcategoryId > 0 && array_key_exists($countSubcategoryId, $subcategoryCounts)) {
            $subcategoryCounts[$countSubcategoryId] = (int) ($subcategoryCountRow['total'] ?? 0);
        }
    }
}

$selectedCategorySubcategoryCounts = [];
if ($selectedCategory !== null) {
    foreach ($selectedCategorySubcategories as $subcategoryRow) {
        $selectedCategorySubcategoryCounts[(int) $subcategoryRow['id']] = $subcategoryCounts[(int) $subcategoryRow['id']] ?? 0;
    }
}

$subcategoryFilterOptions = [];
foreach ($subcategoryGroups as $groupCategoryId => $subcategoryRows) {
    $subcategoryFilterOptions[(int) $groupCategoryId] = [];
    foreach ($subcategoryRows as $subcategoryRow) {
        $subcategoryFilterOptions[(int) $groupCategoryId][] = [
            'id' => (int) $subcategoryRow['id'],
            'label' => document_subcategory_label($subcategoryRow),
            'count' => (int) ($subcategoryCounts[(int) $subcategoryRow['id']] ?? 0),
        ];
    }
}

$paginationWindow = 5;
$paginationStart = max(1, min($page - 2, $totalPages - $paginationWindow + 1));
$paginationEnd = min($totalPages, max($page + 2, $paginationWindow));
if (($paginationEnd - $paginationStart + 1) < $paginationWindow) {
    $paginationStart = max(1, $paginationEnd - $paginationWindow + 1);
}

$paginationItems = [];
if ($totalPages > 0) {
    if ($paginationStart > 1) {
        $paginationItems[] = 1;
        if ($paginationStart > 2) {
            $paginationItems[] = null;
        }
    }

    for ($pageNumber = $paginationStart; $pageNumber <= $paginationEnd; $pageNumber++) {
        $paginationItems[] = $pageNumber;
    }

    if ($paginationEnd < $totalPages) {
        if ($paginationEnd < ($totalPages - 1)) {
            $paginationItems[] = null;
        }
        $paginationItems[] = $totalPages;
    }
}

$buildEbookUrl = static function (array $overrides = []) use ($search, $searchField, $categoryId, $subcategoryId, $fiscalYear, $sort, $itemsPerPage, $subcategoryMap): string {
    $params = [
        'search' => $search,
        'search_field' => $searchField,
        'category_id' => $categoryId > 0 ? $categoryId : null,
        'subcategory_id' => $subcategoryId > 0 ? $subcategoryId : null,
        'fiscal_year' => $fiscalYear > 0 ? $fiscalYear : null,
        'sort' => $sort,
        'items_per_page' => $itemsPerPage,
    ];

    foreach ($overrides as $key => $value) {
        $params[$key] = $value;
    }

    $normalizedCategoryId = isset($params['category_id']) ? (int) $params['category_id'] : 0;
    $normalizedSubcategoryId = isset($params['subcategory_id']) ? (int) $params['subcategory_id'] : 0;
    if ($normalizedCategoryId <= 0) {
        $params['subcategory_id'] = null;
    } elseif (
        $normalizedSubcategoryId > 0
        && (!isset($subcategoryMap[$normalizedSubcategoryId]) || (int) ($subcategoryMap[$normalizedSubcategoryId]['category_id'] ?? 0) !== $normalizedCategoryId)
    ) {
        $params['subcategory_id'] = null;
    }

    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || ($key === 'search_field' && $value === 'all') || ($key === 'sort' && $value === 'latest')) {
            unset($params[$key]);
        }
    }

    $query = http_build_query($params);
    return 'e-Book.php' . ($query !== '' ? '?' . $query : '');
};

$sortLabels = [
    'latest' => 'เรียงตามล่าสุด',
    'year' => 'เรียงตามปีงบประมาณ',
    'name' => 'เรียงตามชื่อเอกสาร',
];

$hasAdvancedFilters = $searchField !== 'all' || $selectedCategory !== null || $selectedSubcategory !== null || $fiscalYear > 0 || $sort !== 'latest';
$showFilterSummary = $selectedCategory !== null || $selectedSubcategory !== null || $fiscalYear > 0 || $search !== '' || $sort !== 'latest';

$cardAccentMap = [
    'article-7' => ['solid' => '#123b5d', 'soft' => 'rgba(18, 59, 93, 0.12)'],
    'article-9-1' => ['solid' => '#1f5f8b', 'soft' => 'rgba(31, 95, 139, 0.12)'],
    'article-9-2' => ['solid' => '#0f766e', 'soft' => 'rgba(15, 118, 110, 0.12)'],
    'article-9-3' => ['solid' => '#3f5f78', 'soft' => 'rgba(63, 95, 120, 0.12)'],
    'article-9-4' => ['solid' => '#8b6a35', 'soft' => 'rgba(139, 106, 53, 0.12)'],
    'article-9-5' => ['solid' => '#53746d', 'soft' => 'rgba(83, 116, 109, 0.12)'],
    'article-9-6' => ['solid' => '#4d6485', 'soft' => 'rgba(77, 100, 133, 0.12)'],
    'article-9-7' => ['solid' => '#736250', 'soft' => 'rgba(115, 98, 80, 0.12)'],
    'article-9-8' => ['solid' => '#31576f', 'soft' => 'rgba(49, 87, 111, 0.12)'],
];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Anuphan:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
        :root {
            color-scheme: light;
            --bg: #f3efe7;
            --bg-soft: #ece6dc;
            --surface: rgba(255, 255, 255, 0.94);
            --surface-2: rgba(247, 243, 236, 0.92);
            --surface-3: #f6f1e8;
            --primary: #1f5f8b;
            --primary-2: #123b5d;
            --primary-3: #0f2c43;
            --accent: #b6924d;
            --accent-2: #8c6b2b;
            --text: #20303c;
            --muted: #6b7782;
            --border: rgba(18, 59, 93, 0.14);
            --border-strong: rgba(18, 59, 93, 0.28);
            --shadow: 0 20px 48px rgba(15, 44, 67, 0.12);
            --shadow-soft: 0 10px 28px rgba(15, 44, 67, 0.08);
            --hero-gradient: linear-gradient(135deg, #123b5d, #1f5f8b);
            --focus-ring: 0 0 0 3px rgba(31, 95, 139, 0.16);
            --radius-control: 14px;
            --radius-panel: 20px;
            --radius-card: 24px;
            --radius-chip: 999px;
            --space-1: 8px;
            --space-2: 12px;
            --space-3: 16px;
            --space-4: 24px;
            --btn-height: 38px;
            --field-height: 44px;
        }
        body {
            font-family: 'Anuphan', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(182, 146, 77, 0.14), transparent 24%),
                radial-gradient(circle at top right, rgba(18, 59, 93, 0.12), transparent 28%),
                linear-gradient(180deg, #f6f2eb 0%, #efe8dd 100%);
            color: var(--text);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-size: 1rem;
            display: flex;
            flex-direction: column;
            line-height: 1.7;
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(rgba(15, 44, 67, 0.035) 0.8px, transparent 0.8px),
                radial-gradient(rgba(182, 146, 77, 0.03) 0.7px, transparent 0.7px);
            background-position: 0 0, 12px 12px;
            background-size: 24px 24px;
            mask-image: linear-gradient(180deg, rgba(0,0,0,0.22), rgba(0,0,0,0.04));
            pointer-events: none;
            z-index: 0;
        }
        #particles-js {
            display: none;
        }
        .container-fluid {
            max-width: 1440px;
            width: calc(100% - 40px);
            padding: var(--space-4);
            margin: var(--space-4) auto;
            background: var(--surface);
            border-radius: 26px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.72);
            position: relative;
            z-index: 1;
            backdrop-filter: blur(14px);
        }
        .container-fluid::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(140deg, rgba(255,255,255,0.82), rgba(18, 59, 93, 0.05), rgba(182, 146, 77, 0.12));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        h1 {
            color: var(--primary-3);
            font-weight: 700;
            font-size: 2.2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .alert-success {
            background: rgba(34, 197, 94, 0.10);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.32);
            border-radius: 16px;
            padding: var(--space-3);
            position: relative;
            margin-top: 63px;
            box-shadow: var(--shadow-soft);
        }
        .alert-danger {
            background: rgba(185, 28, 28, 0.09);
            color: #9f1239;
            border: 1px solid rgba(190, 24, 93, 0.22);
            border-radius: 16px;
            padding: 14px 16px;
            position: relative;
            box-shadow: var(--shadow-soft);
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
            border-radius: 22px;
            border: 1px solid var(--border);
            width: 100%;
            table-layout: auto;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }
        .table th, .table td {
            padding: 14px 16px;
            color: var(--text);
            vertical-align: middle;
            text-align: center;
            font-size: 0.95rem;
            border: 1px solid rgba(139, 92, 246, 0.12);
            line-height: 1.5;
        }
                .table td:nth-child(2) {
            text-align: left;
        }
        .table td:nth-child(3) {
            text-align: center;
        }
.table th {
            background: linear-gradient(135deg, rgba(241, 236, 226, 0.96), rgba(247, 243, 236, 0.98));
            color: var(--primary-3);
            font-weight: 700;
            border-color: rgba(18, 59, 93, 0.08);
            letter-spacing: 0.02em;
        }
        .table tbody tr {
            transition: background 0.2s ease, transform 0.2s ease;
            opacity: 0;
            transform: translateY(20px);
            background: rgba(255, 255, 255, 0.92);
        }
        .table tbody tr.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }
        .table tbody tr:hover {
            background: rgba(244, 240, 232, 0.92);
        }
        .btn {
            border-radius: var(--radius-control);
            padding: var(--space-1) var(--space-3);
            transition: all 0.2s ease;
            border: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: var(--btn-height);
            font-size: 0.9rem;
            gap: var(--space-1);
            max-width: 160px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }
        .btn-sm {
            padding: 4px var(--space-2);
            font-size: 0.82rem;
            height: 32px;
            max-width: 140px;
        }
        .bi {
            line-height: 1;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: #ffffff;
        }
        .btn-primary:hover {
            box-shadow: 0 14px 24px rgba(18, 59, 93, 0.20);
            transform: translateY(-1px);
        }
        .btn-success {
            background: linear-gradient(135deg, #14736b, #0f5c56);
            color: #ffffff;
        }
        .btn-danger {
            background: linear-gradient(135deg, #9f2d2d, #7d1f1f);
            color: #ffffff;
        }
        .btn-secondary {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.94), rgba(244, 239, 231, 0.98));
            color: var(--primary-2);
            border: 1px solid rgba(18, 59, 93, 0.12);
            box-shadow: none;
        }
        .btn-secondary:hover {
            background: rgba(244, 239, 231, 0.98);
            color: var(--primary-3);
            border-color: rgba(18, 59, 93, 0.18);
        }
        .btn-warning {
            background: linear-gradient(135deg, #d9bb74, #b6924d);
            color: #362913;
        }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(18, 59, 93, 0.14);
            color: var(--text);
            border-radius: var(--radius-control);
            transition: all 0.2s;
            font-weight: 500;
            min-height: var(--field-height);
            font-size: 0.95rem;
            padding: 10px 14px;
            box-shadow: inset 0 1px 2px rgba(18, 59, 93, 0.05);
        }
        .form-control:focus, .form-select:focus {
            border-color: rgba(31, 95, 139, 0.42);
            box-shadow: var(--focus-ring);
            background: #fff;
        }
        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: var(--space-2);
            align-items: center;
        }
        .pagination .page-link {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(18, 59, 93, 0.14);
            color: var(--primary-2);
            margin: 0 5px;
            border-radius: var(--radius-control);
            transition: all 0.2s;
            font-weight: 600;
            font-size: 0.9rem;
            min-width: 36px;
            min-height: 36px;
            text-align: center;
            box-shadow: 0 6px 14px rgba(15, 44, 67, 0.06);
        }
        .pagination .page-link:hover,
        .pagination .page-item.active .page-link {
            background: var(--hero-gradient);
            color: #fff;
            border-color: transparent;
        }
        .sidebar {
            background: rgba(255, 255, 255, 0.98);
            border-right: 1px solid var(--border);
            position: absolute;
            top: 0;
            left: -260px;
            width: 260px;
            max-width: 80vw;
            height: 100%;
            transition: left 0.3s ease-in-out;
            z-index: 1000;
            box-shadow: 8px 0 24px rgba(15, 44, 67, 0.12);
            backdrop-filter: blur(14px);
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
            background: var(--hero-gradient);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 0 14px 14px 0;
            z-index: 1001;
            transition: left 0.3s ease-in-out;
            box-shadow: 0 10px 20px rgba(18, 59, 93, 0.18);
        }
        .sidebar.active ~ .sidebar-toggle {
            left: 260px;
        }
        .sidebar-content {
            padding: var(--space-4);
            display: flex;
            flex-direction: column;
            gap: var(--space-2);
            margin-top: 60px;
        }
        .sidebar-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-2);
            padding: 10px 12px;
            text-decoration: none;
            border-radius: 14px;
            font-size: 0.95rem;
            transition: background 0.2s, transform 0.2s;
            font-weight: 600;
        }
        .sidebar-item:hover {
            background: rgba(244, 239, 231, 0.96);
            color: var(--primary-3);
            transform: translateX(4px);
        }
        .sidebar-item i {
            font-size: 1.1rem;
            color: var(--accent-2);
        }
        .popup-overlay {
            position: fixed;
            inset: 0;
            width: 100vw;
            min-height: 100vh;
            background: rgba(15, 23, 42, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-4);
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
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(247,243,236,0.98));
            border: 1px solid rgba(18, 59, 93, 0.14);
            padding: 20px;
            border-radius: 20px;
            width: min(420px, 100%);
            max-width: 420px;
            box-shadow: var(--shadow);
            margin: auto;
        }
        .popup-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary-2);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            inset: 0;
            width: 100vw;
            min-height: 100vh;
            background: rgba(15, 23, 42, 0.85);
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }
        .modal-content {
            display: block;
            width: auto;
            height: auto;
            max-width: min(92vw, 1200px);
            max-height: calc(100vh - 80px);
            border-radius: 12px;
            box-shadow: var(--shadow);
            object-fit: contain;
            margin: auto;
        }
        .close {
            position: fixed;
            top: 18px;
            right: 22px;
            color: #fff;
            font-size: 2rem;
            line-height: 1;
            cursor: pointer;
            z-index: 1001;
        }
        .image-thumbnail {
            cursor: pointer;
            transition: transform 0.2s ease;
            border-radius: 12px;
            box-shadow: 0 8px 18px rgba(139, 92, 246, 0.12);
        }
        .image-thumbnail:hover {
            transform: scale(1.05);
        }
        @media (max-width: 992px) {
            .container-fluid { width: calc(100% - 24px); padding: 18px; margin: 18px auto; }
            .input-group { flex-direction: column; align-items: stretch; }
            .form-control, .form-select, .btn { width: 100%; max-width: 100%; }
            h1 { font-size: 2rem; }
        }
        @media (max-width: 768px) {
            .container-fluid { width: calc(100% - 20px); padding: 14px; margin: 12px auto; border-radius: 20px; }
            .form-card { padding: 12px; }
            .sidebar { position: fixed; height: 100vh; }
            .sidebar-toggle { position: fixed; }
            .pagination { flex-wrap: wrap; gap: 6px; }
            .alert-success { margin-top: 16px; }
        }
        @media (max-width: 576px) {
            body { font-size: 0.92rem; }
            .container-fluid { width: calc(100% - 16px); padding: 14px; margin: 10px auto; }
            .btn { height: 34px; font-size: 0.82rem; }
            .btn-sm { height: 30px; font-size: 0.74rem; }
            .table th, .table td { font-size: 0.85rem; padding: 8px; }
            .table-toolbar { flex-direction: column; align-items: stretch; }
            .table-toolbar label { width: 100%; }
            .table-toolbar .form-select { width: 100%; max-width: 100%; }
            .action-buttons { width: 100%; }
            .action-buttons .btn { width: 100%; max-width: 100%; }
        }
        @media (max-width: 640px) {
            .table thead .table-head-row { display: none; }
            .table thead, .table thead tr, .table thead th { display: block; width: 100%; }
            .table tbody, .table tr, .table td { display: block; width: 100%; }
            .table tr {
                margin-bottom: 12px;
                border: 1px solid rgba(139, 92, 246, 0.14);
                border-radius: 18px;
                overflow: hidden;
                background: rgba(255, 255, 255, 0.98);
                box-shadow: var(--shadow-soft);
            }
            .table td {
                text-align: right;
                padding-left: 46%;
                padding-top: 28px;
                padding-bottom: 10px;
                position: relative;
                border: none;
                word-break: break-word;
            }
            .table td:not(:last-child) {
                border-bottom: 1px solid rgba(139, 92, 246, 0.12);
            }
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 12px;
                top: 8px;
                width: 44%;
                text-align: left;
                font-weight: 600;
                color: var(--muted);
            }
            .table td[colspan] {
                text-align: center;
                padding-left: 12px;
                padding-top: 12px;
            }
            .table td[colspan]::before { display: none; }
            .table td[data-label="ชื่อ"] span,
            .table td[data-label="รายละเอียด"] {
                display: block;
            }
            .table td .btn { width: 100%; }
            .table td .btn + .btn { margin-top: 6px; }
            .image-thumbnail { max-width: 120px; width: 100%; height: auto; }
        }
        .role-banner {
            position: absolute;
            top: 16px;
            right: 18px;
            z-index: 1100;
            padding: 10px 16px;
            border-radius: 999px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(18, 59, 93, 0.12);
            background: rgba(255, 255, 255, 0.92);
            color: var(--primary-2);
            backdrop-filter: blur(12px);
        }
        .role-banner i {
            font-size: 0.95rem;
        }
        .role-admin {
            background: linear-gradient(135deg, rgba(243, 236, 222, 0.96), rgba(255, 255, 255, 0.98));
        }
        .role-user {
            background: linear-gradient(135deg, rgba(243, 236, 222, 0.96), rgba(255, 255, 255, 0.98));
            border-color: rgba(18, 59, 93, 0.12);
            color: #0f172a;
        }
        @media (max-width: 768px) {
            .role-banner {
                left: 10px;
                right: auto;
                top: 10px;
                border-radius: 999px;
            }
        }
        .form-card {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 244, 238, 0.96));
            border: 1px solid rgba(18, 59, 93, 0.10);
            border-radius: var(--radius-panel);
            padding: 20px 22px;
            box-shadow: var(--shadow-soft);
            position: relative;
            backdrop-filter: blur(14px);
        }
        .form-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            border-radius: 20px 20px 0 0;
            background: linear-gradient(90deg, var(--accent), var(--primary-2), var(--accent));
            opacity: 0.9;
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
            background: linear-gradient(135deg, #b6924d, #8c6b2b);
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
        .item-meta-group {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        .item-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 5px 11px;
            font-size: 0.74rem;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .item-pill-category {
            background: rgba(18, 59, 93, 0.08);
            color: var(--primary-2);
            border-color: rgba(18, 59, 93, 0.10);
        }
        .item-pill-year {
            background: rgba(182, 146, 77, 0.16);
            color: var(--accent-2);
            border-color: rgba(182, 146, 77, 0.16);
        }
        .article-filter-note {
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: normal;
            text-transform: none;
            color: var(--muted);
        }
        .article-filter-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 8px;
        }
        .article-filter-link {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 4px;
            min-height: 74px;
            padding: 10px 14px;
            border-radius: var(--radius-chip);
            border: 1px solid rgba(18, 59, 93, 0.10);
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(246,241,232,0.96));
            color: var(--primary-2);
            text-decoration: none;
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1.35;
            text-align: left;
            box-shadow: 0 8px 20px rgba(15, 44, 67, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .article-filter-link-all {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            min-height: var(--btn-height);
            gap: 10px;
        }
        .article-filter-link-label {
            display: block;
            flex: 1 1 auto;
            min-width: 0;
            overflow: visible;
            text-overflow: clip;
            white-space: normal;
            word-break: normal;
            line-height: 1.3;
        }
        .article-filter-link-detail {
            display: block;
            font-size: 0.72rem;
            line-height: 1.45;
            color: var(--muted);
            font-weight: 600;
            white-space: normal;
            word-break: break-word;
            opacity: 0.96;
        }
        .article-filter-link-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 26px;
            height: 22px;
            padding: 0 8px;
            border-radius: 999px;
            background: rgba(182, 146, 77, 0.14);
            color: inherit;
            font-size: 0.72rem;
            line-height: 1;
            flex: 0 0 auto;
        }
        .article-filter-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(15, 44, 67, 0.10);
            color: var(--primary-3);
        }
        .article-filter-link::after {
            content: '';
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 6px;
            height: 2px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.8);
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.22s ease;
        }
        .article-filter-link.active {
            background: var(--hero-gradient);
            border-color: transparent;
            color: #ffffff;
            box-shadow: 0 18px 30px rgba(18, 59, 93, 0.20);
        }
        .article-filter-link.active .article-filter-link-count {
            background: rgba(255, 255, 255, 0.20);
            color: #ffffff;
        }
        .article-filter-link.active .article-filter-link-detail {
            color: rgba(255, 255, 255, 0.86);
        }
        .article-filter-link.active::after {
            transform: scaleX(1);
        }
        .filter-status-row {
            order: 3;
            margin-top: 0 !important;
            margin-bottom: 0.35rem;
        }
        .search-controls-row {
            order: 4;
        }
        .category-detail-card {
            border: 1px solid rgba(18, 59, 93, 0.10);
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(243, 236, 222, 0.8), rgba(255, 255, 255, 0.92));
            padding: 16px 18px;
            order: 3;
            margin-bottom: 0.35rem;
        }
        .category-detail-title {
            color: var(--primary-2);
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .category-detail-list {
            margin: 0;
            padding-left: 0;
            list-style: none;
            color: var(--text);
            display: grid;
            gap: 8px;
        }
        .category-detail-list li + li {
            margin-top: 0;
        }
        .category-detail-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 14px;
            text-decoration: none;
            color: var(--text);
            border: 1px solid rgba(18, 59, 93, 0.08);
            background: rgba(255, 255, 255, 0.86);
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .category-detail-link > span:first-child {
            flex: 1 1 auto;
            min-width: 0;
            line-height: 1.45;
        }
        .category-detail-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(15, 44, 67, 0.08);
            color: var(--primary-3);
        }
        .category-detail-link.active {
            background: var(--hero-gradient);
            border-color: transparent;
            color: #ffffff;
        }
        .category-detail-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 24px;
            padding: 0 8px;
            border-radius: 999px;
            background: rgba(182, 146, 77, 0.12);
            font-size: 0.74rem;
            font-weight: 700;
            flex: 0 0 auto;
        }
        .category-detail-link.active .category-detail-count {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }
        .item-pill-subcategory {
            background: rgba(83, 116, 109, 0.12);
            color: #35574f;
            border-color: rgba(83, 116, 109, 0.18);
        }
        .item-meta-line {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .library-heading {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }
        .library-heading-badge {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: linear-gradient(145deg, var(--primary-2), #234a68);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 16px 30px rgba(18, 59, 93, 0.18);
            border: 1px solid rgba(182, 146, 77, 0.28);
        }
        .section-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--accent-2);
        }
        .section-eyebrow::before {
            content: '';
            width: 26px;
            height: 1px;
            background: currentColor;
            opacity: 0.55;
        }
        .section-title {
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--primary-3);
            line-height: 1.2;
            margin-top: 6px;
            margin-bottom: 4px;
        }
        .section-subtitle {
            color: #5f6b74;
            max-width: 680px;
        }
        .action-buttons .btn {
            min-width: 140px;
        }
        .table-toolbar-row th {
            background: rgba(232, 240, 239, 0.5);
            color: var(--primary-3);
        }
        .table-toolbar label {
            color: var(--muted);
        }
        .records-summary {
            color: var(--primary-2);
            font-weight: 600;
        }
        .results-shell {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .results-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 18px;
            border: 1px solid rgba(18, 59, 93, 0.10);
            border-radius: var(--radius-panel);
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(245,240,232,0.92));
            box-shadow: var(--shadow-soft);
        }
        .results-toolbar-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-3);
        }
        .results-toolbar-subtitle {
            color: var(--muted);
            font-size: 0.9rem;
        }
        .page-size-form {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .page-size-form label {
            color: var(--muted);
            font-weight: 600;
        }
        .page-size-form .form-select {
            min-width: 88px;
        }
        .page-link-nav {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .page-link-label {
            font-size: 0.82rem;
        }
        .page-item-ellipsis .page-link {
            background: transparent;
            border-color: transparent;
            box-shadow: none;
            color: var(--muted);
            pointer-events: none;
        }
        .document-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 18px;
        }
        .document-card {
            --card-accent: var(--primary);
            --card-accent-soft: rgba(139, 92, 246, 0.12);
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 18px 18px 18px 24px;
            background: linear-gradient(180deg, rgba(255,255,255,0.99), rgba(248,244,238,0.96));
            border: 1px solid rgba(18, 59, 93, 0.10);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            transition: transform 0.24s ease, box-shadow 0.24s ease, opacity 0.34s ease;
        }
        .document-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            background: linear-gradient(180deg, var(--card-accent), var(--card-accent));
        }
        .document-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--card-accent-soft), transparent 35%);
            opacity: 0.4;
            pointer-events: none;
        }
        .document-card > * {
            position: relative;
            z-index: 1;
        }
        .document-card.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .document-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 22px 38px rgba(15, 44, 67, 0.12);
        }
        .document-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }
        .document-card-index {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: var(--card-accent-soft);
            color: var(--card-accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 700;
            flex: 0 0 auto;
        }
        .document-card-header {
            flex: 1;
            min-width: 0;
        }
        .document-card-title-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            flex-wrap: wrap;
        }
        .document-card-title {
            font-size: 1.04rem;
            line-height: 1.45;
            font-weight: 700;
            color: var(--primary-3);
            margin: 0;
        }
        .document-card-subtitle {
            color: var(--primary-2);
            font-size: 0.88rem;
            margin-top: 6px;
        }
        .document-card-body {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 96px;
            gap: 16px;
            align-items: start;
        }
        .document-card-description {
            color: var(--text);
            font-size: 0.93rem;
            line-height: 1.7;
            margin: 0;
            word-break: break-word;
            transition: max-height 0.2s ease;
        }
        .document-card-details {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .document-card-insights {
            display: none !important;
        }
        .document-card-media {
            width: 96px;
            min-height: 120px;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(18, 59, 93, 0.06), rgba(182, 146, 77, 0.10));
            border: 1px solid rgba(18, 59, 93, 0.10);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .document-card-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .document-card-media-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            text-align: center;
            padding: 12px;
        }
        .document-card-media-placeholder i {
            font-size: 1.3rem;
            color: var(--card-accent);
        }
        .document-card-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .document-card-actions .btn {
            flex: 1 1 140px;
            max-width: none;
        }
        .document-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-top: 14px;
            border-top: 1px solid rgba(18, 59, 93, 0.10);
        }
        .document-card-footer-meta {
            color: var(--muted);
            font-size: 0.84rem;
            font-weight: 600;
        }
        .document-empty {
            padding: 34px 20px;
            border: 1px dashed rgba(18, 59, 93, 0.18);
            border-radius: var(--radius-panel);
            background: rgba(250, 247, 241, 0.96);
            text-align: center;
            color: var(--muted);
            box-shadow: var(--shadow-soft);
        }
        .filter-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: calc(var(--btn-height) - 4px);
            padding: 6px 12px;
            border-radius: var(--radius-chip);
            text-decoration: none;
            background: rgba(255, 255, 255, 0.96);
            color: var(--primary-2);
            border: 1px solid rgba(18, 59, 93, 0.12);
            font-size: 0.82rem;
            font-weight: 700;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .filter-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(15, 44, 67, 0.08);
            color: var(--primary-3);
        }
        .filter-status-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 2px;
        }
        .filter-status-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-filter-reset {
            min-width: 124px;
        }
        .search-controls-primary {
            align-items: stretch;
        }
        .advanced-filters {
            margin-top: 4px;
        }
        .mobile-filter-toggle-wrap {
            display: none;
        }
        .mobile-filter-toggle {
            max-width: none;
        }
        .btn-soft {
            background: linear-gradient(180deg, rgba(255,255,255,0.96), rgba(246,241,232,0.96));
            color: var(--primary-2);
            border: 1px solid rgba(18, 59, 93, 0.12);
            box-shadow: none;
        }
        .btn-soft:hover {
            background: rgba(244, 239, 231, 0.96);
            color: var(--primary-3);
        }
        .btn-soft-danger {
            color: #8a2f2f;
            border-color: rgba(159, 45, 45, 0.16);
        }
        .btn-soft-danger:hover {
            background: rgba(250, 240, 238, 0.96);
            color: #6d1f1f;
        }
        .btn-action-main {
            max-width: none;
            min-width: 180px;
            min-height: var(--btn-height);
        }
        .document-card-actions-primary {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .document-card-actions-secondary {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }
        .document-card-footer-meta-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .document-card-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.86rem;
            color: var(--primary-2);
            font-weight: 600;
        }
        .document-card-description.is-collapsed {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
        }
        .document-card-description-toggle {
            width: fit-content;
            padding: 0;
            border: none;
            background: transparent;
            color: var(--accent-2);
            font-size: 0.84rem;
            font-weight: 700;
            cursor: pointer;
        }
        .document-card-description-toggle:hover {
            color: var(--primary-2);
        }
        mark {
            background: rgba(182, 146, 77, 0.26);
            color: inherit;
            padding: 0 2px;
            border-radius: 4px;
        }
        .document-empty-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 14px;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(18, 59, 93, 0.12), rgba(182, 146, 77, 0.18));
            color: var(--primary-2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }
        .document-empty-title {
            font-size: 1.08rem;
            font-weight: 700;
            color: var(--primary-3);
            margin-bottom: 6px;
        }
        .document-empty-text {
            max-width: 560px;
            margin: 0 auto;
        }
        .document-empty-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 16px;
        }
        .visually-hidden-desktop {
            display: none;
        }
        .btn:focus-visible,
        .form-control:focus-visible,
        .form-select:focus-visible,
        .article-filter-link:focus-visible,
        .filter-chip:focus-visible,
        .sidebar-item:focus-visible,
        .page-link:focus-visible,
        .image-thumbnail:focus-visible {
            outline: none;
            box-shadow: var(--focus-ring);
        }
        @media (max-width: 768px) {
            .library-heading {
                align-items: center;
            }
            .library-heading-badge {
                width: 44px;
                height: 44px;
                border-radius: 14px;
            }
            .section-title {
                font-size: 1.35rem;
            }
            .document-card-top,
            .document-card-footer,
            .results-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .document-card-body {
                grid-template-columns: 1fr;
            }
            .filter-status-row {
                align-items: flex-start;
            }
            .document-card-actions-secondary {
                justify-content: flex-start;
            }
            .page-link-label {
                display: none;
            }
        }
        @media (max-width: 991px) {
            .mobile-filter-toggle-wrap {
                display: grid;
            }
            .advanced-filters {
                display: none;
            }
            .advanced-filters.is-open {
                display: flex;
            }
        }
        @media (min-width: 992px) {
            .mobile-filter-toggle-wrap {
                display: none;
            }
            .advanced-filters {
                display: flex !important;
            }
        }
        @media (max-width: 576px) {
            .filter-chip-row,
            .filter-status-actions,
            .document-empty-actions {
                width: 100%;
            }
            .filter-chip,
            .document-empty-actions .btn {
                width: 100%;
                justify-content: space-between;
                max-width: none;
            }
            .document-card-actions-secondary,
            .document-card-actions-primary {
                width: 100%;
            }
            .document-card-actions-secondary .btn,
            .document-card-actions-primary .btn,
            .btn-filter-reset {
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div id="particles-js"></div>
    <?= $roleBanner ?>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-2"></i><?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if (isGuest() || isUser() || isAdmin()): ?>
    <div class="sidebar">
        <div class="sidebar-content"> 
            <a href="#" id="openPopup" class="sidebar-item"><i class="bi bi-person-badge"></i> เข้าสู่ระบบสำหรับเจ้าหน้าที่</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="UMS.php" class="sidebar-item"><i class="bi bi-people"></i> ระบบจัดการสมาชิก</a>
            <a href="stats.php" class="sidebar-item"><i class="bi bi-bar-chart-line"></i> สถิติผู้เยี่ยมชมเว็บไซต์</a>
            <a href="login_logs.php" class="sidebar-item"><i class="bi bi-box-arrow-in-right"></i> บันทึกการลงชื่อเข้าใช้</a>
            <a href="hidden_items.php" class="sidebar-item"><i class="bi bi-archive"></i> กู้คืนข้อมูล</a>
            <?php endif; ?>
            <a href="https://dc.phsmun.go.th/" class="sidebar-item"><i class="bi bi-house-door"></i> หน้าหลัก</a>
        </div>
    </div>
    <div class="sidebar-toggle"><i class="bi bi-list"></i></div>

    <div id="customPopup" class="popup-overlay">
        <div class="popup-box">
            <div class="popup-header">
                <h5 class="popup-title"><i class="bi bi-box-arrow-in-right me-2"></i>ยินยันการเข้าสู่ระบบ</h5>
                <button type="button" class="btn-close" id="closePopup" aria-label="Close"></button>
            </div>
            <div class="popup-body">คุณต้องการเข้าสู่ระบบสำหรับเจ้าหน้าที่หรือไม่?</div>
            <div class="popup-footer d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-secondary" id="closePopupBtn"><i class="bi bi-x-lg me-2"></i>ยกเลิก</button>
                <a href="login.php" class="btn btn-success"><i class="bi bi-check2 me-2"></i>ยืนยัน</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="container-fluid">
        <!-- <h1><i class="fas fa-book-open me-2"></i>ชั้นหนังสืออิเล็กทรอนิกส์</h1> -->
        <!-- <div class="form-card d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3" style="display:none;">
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
        </div> -->

        <div class="form-card mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                <div class="library-heading">
                    <div class="library-heading-badge"><i class="bi bi-journal-bookmark"></i></div>
                    <div>
                        <div class="section-eyebrow">Official Information Center</div>
                        <div class="section-title">ชั้นหนังสืออิเล็กทรอนิกส์</div>
                        <div class="section-subtitle">หมวดเอกสารตามมาตรา 7 และมาตรา 9(1)-(8) ในรูปแบบที่ค้นหาและตรวจสอบได้ง่าย</div>
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-2 action-buttons">
                    <?php if (isAdmin() || isUser()): ?><a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> เพิ่มข้อมูล</a><?php endif; ?>
                    <?php if (!isGuest()): ?><a href="logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a><?php endif; ?>
                </div>
            </div>
            <form class="d-flex flex-column gap-2 w-100" method="GET" action="e-Book.php" id="ebookFiltersForm">
                <div class="article-filter-note">กรองตามหัวข้อมาตรา</div>
                <div class="article-filter-bar">
                    <a href="<?= htmlspecialchars($buildEbookUrl(['category_id' => null, 'page' => null])) ?>" class="article-filter-link <?= $selectedCategory === null ? 'active' : '' ?>">ทั้งหมด</a>
                    <?php foreach ($categories as $category): ?>
                        <?php
                            $isActiveArticle = (int) $categoryId === (int) $category['id'];
                            $articleHeading = trim((string) ($category['reference_label'] ?? ''));
                            if ($articleHeading === '') {
                                $articleHeading = document_category_label($category);
                            }
                        ?>
                        <a href="<?= htmlspecialchars($buildEbookUrl(['category_id' => (int) $category['id'], 'page' => null])) ?>" class="article-filter-link <?= $isActiveArticle ? 'active' : '' ?>" title="<?= htmlspecialchars(document_category_label($category)) ?>"><?= htmlspecialchars($articleHeading) ?></a>
                    <?php endforeach; ?>
                </div>

                <div class="row g-2 search-controls-row search-controls-primary">
                    <div class="col-lg-8 col-md-7">
                        <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อเอกสาร รายละเอียด หรือหมวดกฎหมาย" value="<?= htmlspecialchars($search) ?>" maxlength="100" aria-label="ค้นหาชื่อเอกสาร รายละเอียด หรือหมวดกฎหมาย">
                    </div>
                    <div class="col-lg-2 col-md-3 d-grid mobile-filter-toggle-wrap">
                        <button type="button" class="btn btn-secondary mobile-filter-toggle" id="toggleAdvancedFilters" aria-expanded="<?= $hasAdvancedFilters ? 'true' : 'false' ?>" aria-controls="advancedFilters">
                            <i class="bi bi-sliders"></i> ตัวกรองเพิ่มเติม
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                    </div>
                </div>

                <div class="row g-2 search-controls-row advanced-filters <?= $hasAdvancedFilters ? 'is-open' : '' ?>" id="advancedFilters">
                    <div class="col-lg-3 col-md-6">
                        <select name="search_field" class="form-select" aria-label="เลือกขอบเขตการค้นหา">
                            <option value="all" <?= $searchField === 'all' ? 'selected' : '' ?>>ค้นหาทั้งหมด</option>
                            <option value="name" <?= $searchField === 'name' ? 'selected' : '' ?>>เฉพาะชื่อ</option>
                            <option value="details" <?= $searchField === 'details' ? 'selected' : '' ?>>เฉพาะรายละเอียด</option>
                            <option value="category" <?= $searchField === 'category' ? 'selected' : '' ?>>หมวดกฎหมาย</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <select name="category_id" class="form-select" aria-label="เลือกมาตราหรือหมวดกฎหมาย">
                            <option value="">ทุกหมวดกฎหมาย</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= (int) $categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(document_category_label($category)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <select name="subcategory_id" class="form-select" id="subcategoryFilter" aria-label="เลือกหมวดย่อยตามมาตรา" <?= $selectedCategory === null ? 'disabled' : '' ?>>
                            <option value=""><?= $selectedCategory === null ? 'เลือกมาตราก่อน' : 'ทุกรายละเอียดย่อย' ?></option>
                            <?php foreach ($selectedCategorySubcategories as $subcategory): ?>
                                <?php $subcategoryCount = (int) ($selectedCategorySubcategoryCounts[(int) $subcategory['id']] ?? 0); ?>
                                <option value="<?= (int) $subcategory['id'] ?>" <?= (int) $subcategoryId === (int) $subcategory['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(document_subcategory_label($subcategory)) ?> (<?= $subcategoryCount ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <select name="fiscal_year" class="form-select" aria-label="เลือกปีงบประมาณ">
                            <option value="">ทุกปีงบประมาณ</option>
                            <?php foreach ($fiscalYears as $year): ?>
                                <option value="<?= (int) $year ?>" <?= $fiscalYear === (int) $year ? 'selected' : '' ?>>ปี <?= (int) $year ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <select name="sort" class="form-select" aria-label="เลือกการเรียงข้อมูล">
                            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>เรียงตามล่าสุด</option>
                            <option value="year" <?= $sort === 'year' ? 'selected' : '' ?>>เรียงตามปีงบประมาณ</option>
                            <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>เรียงตามชื่อเอกสาร</option>
                        </select>
                    </div>
                </div>

                <?php if ($showFilterSummary): ?>
                <div class="filter-status-row">
                    <div class="filter-chip-row" aria-label="ตัวกรองที่เลือก">
                        <?php if ($search !== ''): ?>
                            <a href="<?= htmlspecialchars($buildEbookUrl(['search' => null, 'search_field' => 'all', 'page' => null])) ?>" class="filter-chip" aria-label="ลบตัวกรองคำค้นหา">
                                <span>คำค้นหา: <?= htmlspecialchars($search) ?></span>
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($selectedCategory !== null): ?>
                            <a href="<?= htmlspecialchars($buildEbookUrl(['category_id' => null, 'page' => null])) ?>" class="filter-chip" aria-label="ลบตัวกรองหมวดกฎหมาย">
                                <span><?= htmlspecialchars(document_category_label($selectedCategory)) ?></span>
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($selectedSubcategory !== null): ?>
                            <a href="<?= htmlspecialchars($buildEbookUrl(['subcategory_id' => null, 'page' => null])) ?>" class="filter-chip" aria-label="ลบตัวกรองรายละเอียดย่อย">
                                <span><?= htmlspecialchars(document_subcategory_label($selectedSubcategory)) ?></span>
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($fiscalYear > 0): ?>
                            <a href="<?= htmlspecialchars($buildEbookUrl(['fiscal_year' => null, 'page' => null])) ?>" class="filter-chip" aria-label="ลบตัวกรองปีงบประมาณ">
                                <span>ปี <?= htmlspecialchars((string) $fiscalYear) ?></span>
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($sort !== 'latest'): ?>
                            <a href="<?= htmlspecialchars($buildEbookUrl(['sort' => 'latest', 'page' => null])) ?>" class="filter-chip" aria-label="ลบการเรียงข้อมูล">
                                <span><?= htmlspecialchars($sortLabels[$sort] ?? 'การเรียงข้อมูล') ?></span>
                                <i class="bi bi-x-lg"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="filter-status-actions">
                        <a href="<?= htmlspecialchars($buildEbookUrl(['search' => null, 'search_field' => 'all', 'category_id' => null, 'subcategory_id' => null, 'fiscal_year' => null, 'sort' => 'latest', 'page' => null])) ?>" class="btn btn-secondary btn-filter-reset">
                            <i class="bi bi-arrow-counterclockwise"></i> ล้างทั้งหมด
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($selectedCategory !== null && $selectedCategorySubcategories !== []): ?>
                <div class="category-detail-card">
                    <div class="category-detail-title">รายละเอียดย่อยใน <?= htmlspecialchars($selectedCategory['reference_label'] ?? '') ?></div>
                    <ul class="category-detail-list">
                        <?php foreach ($selectedCategorySubcategories as $subcategory): ?>
                            <?php
                                $detailSubcategoryId = (int) $subcategory['id'];
                                $isActiveSubcategory = $selectedSubcategory !== null && (int) $selectedSubcategory['id'] === $detailSubcategoryId;
                                $detailSubcategoryCount = (int) ($selectedCategorySubcategoryCounts[$detailSubcategoryId] ?? 0);
                            ?>
                            <li>
                                <a href="<?= htmlspecialchars($buildEbookUrl(['category_id' => $categoryId, 'subcategory_id' => $detailSubcategoryId, 'page' => null])) ?>" class="category-detail-link <?= $isActiveSubcategory ? 'active' : '' ?>">
                                    <span><?= htmlspecialchars(document_subcategory_label($subcategory)) ?></span>
                                    <span class="category-detail-count"><?= $detailSubcategoryCount ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <div class="results-shell">
            <div class="results-toolbar">
                <div>
                    <div class="results-toolbar-title">รายการเอกสาร</div>
                    <div class="results-toolbar-subtitle">
                        <?= $totalItems > 0 ? 'แสดง ' . $visibleFrom . '-' . $visibleTo . ' จากทั้งหมด ' . $totalItems . ' รายการ' : 'ไม่มีข้อมูลเอกสารในเงื่อนไขที่เลือก' ?>
                    </div>
                </div>
                <form class="page-size-form" method="GET">
                    <label for="items_per_page" class="mb-0">แสดงข้อมูลต่อหน้า</label>
                    <select id="items_per_page" name="items_per_page" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="เลือกจำนวนรายการต่อหน้า">
                        <option value="30" <?= $itemsPerPage == 30 ? 'selected' : '' ?>>30</option>
                        <option value="50" <?= $itemsPerPage == 50 ? 'selected' : '' ?>>50</option>
                        <option value="70" <?= $itemsPerPage == 70 ? 'selected' : '' ?>>70</option>
                        <option value="100" <?= $itemsPerPage == 100 ? 'selected' : '' ?>>100</option>
                    </select>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <input type="hidden" name="search_field" value="<?= htmlspecialchars($searchField) ?>">
                    <input type="hidden" name="category_id" value="<?= htmlspecialchars((string) $categoryId) ?>">
                    <input type="hidden" name="subcategory_id" value="<?= htmlspecialchars((string) $subcategoryId) ?>">
                    <input type="hidden" name="fiscal_year" value="<?= htmlspecialchars((string) $fiscalYear) ?>">
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                </form>
            </div>

            <?php if ($items !== []): ?>
            <div class="document-grid">
                <?php foreach ($items as $index => $item): ?>
                <?php
                    $createdAt = $item['created_at'] ?? null;
                    $isNew = $createdAt && (strtotime($createdAt) >= (time() - 2 * 24 * 60 * 60));
                    $categoryLabel = trim(($item['category_reference'] ?? '') . ' ' . ($item['category_title'] ?? ''));
                    $subcategoryLabel = trim((string) ($item['subcategory_label'] ?? ''));
                    $detailsText = trim((string) ($item['details'] ?? ''));
                    $normalizedDetailText = preg_replace('/\s+/u', ' ', $detailsText) ?? $detailsText;
                    $shouldCollapseDescription = ebook_text_length($normalizedDetailText) > 180;
                    $documentTitleHtml = ebook_highlight_html((string) ($item['name'] ?? ''), $search);
                    $subcategoryLabelHtml = $subcategoryLabel !== '' ? ebook_highlight_html($subcategoryLabel, $search) : '';
                    $descriptionHtml = ebook_highlight_html($detailsText, $search, true);
                    $documentDateDisplay = !empty($item['document_date']) ? ebook_format_date((string) $item['document_date']) : '-';
                    $createdAtDisplay = !empty($item['created_at']) ? ebook_format_date((string) $item['created_at'], true) : '-';
                    $pdfStatusLabel = !empty($item['pdf_file']) ? 'พร้อมใช้งาน' : 'ยังไม่มี';
                    $coverStatusLabel = !empty($item['image']) ? 'มีภาพปก' : 'ไม่มีภาพปก';
                    $accent = $cardAccentMap[$item['category_code'] ?? ''] ?? ['solid' => '#8b5cf6', 'soft' => 'rgba(139, 92, 246, 0.12)'];
                    $cardStyle = '--card-accent:' . $accent['solid'] . '; --card-accent-soft:' . $accent['soft'] . ';';
                    $documentMetaText = !empty($item['document_date'])
                        ? 'วันที่เอกสาร: ' . htmlspecialchars((string) $item['document_date'])
                        : 'บันทึกเมื่อ: ' . htmlspecialchars((string) $item['created_at']);
                    $documentMetaText = !empty($item['document_date'])
                        ? 'วันที่เอกสาร: ' . $documentDateDisplay
                        : 'บันทึกเมื่อ: ' . $createdAtDisplay;
                ?>
                <article class="document-card" style="<?= htmlspecialchars($cardStyle) ?>">
                    <div class="document-card-top">
                        <div class="document-card-index"><?= $offset + $index + 1 ?></div>
                        <div class="document-card-header">
                            <div class="document-card-title-row">
                                <h2 class="document-card-title"><?= $documentTitleHtml ?></h2>
                                <?php if ($isNew): ?>
                                    <span class="badge badge-new">ใหม่ล่าสุด</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($subcategoryLabel !== ''): ?>
                                <div class="document-card-subtitle"><?= $subcategoryLabelHtml ?></div>
                            <?php endif; ?>
                            <?php if ($categoryLabel !== '' || !empty($item['fiscal_year'])): ?>
                                <div class="item-meta-group">
                                    <?php if ($categoryLabel !== ''): ?>
                                        <span class="item-pill item-pill-category"><?= htmlspecialchars($categoryLabel) ?></span>
                                    <?php endif; ?>
                                    <?php if ($subcategoryLabel !== ''): ?>
                                        <span class="item-pill item-pill-subcategory"><?= htmlspecialchars($subcategoryLabel) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($item['fiscal_year'])): ?>
                                        <span class="item-pill item-pill-year">ปี <?= htmlspecialchars((string) $item['fiscal_year']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="document-card-body">
                        <div class="document-card-details">
                            <p id="document-description-<?= (int) $item['id'] ?>" class="document-card-description <?= $shouldCollapseDescription ? 'is-collapsed' : '' ?>" title="<?= htmlspecialchars($detailsText) ?>"><?= $descriptionHtml ?></p>
                            <?php if ($shouldCollapseDescription): ?>
                                <button type="button" class="document-card-description-toggle" data-description-toggle="document-description-<?= (int) $item['id'] ?>" data-more-label="อ่านรายละเอียดเพิ่ม" data-less-label="ซ่อนรายละเอียด" aria-controls="document-description-<?= (int) $item['id'] ?>" aria-expanded="false">
                                    อ่านรายละเอียดเพิ่ม
                                </button>
                            <?php endif; ?>
                            <div class="document-card-insights">
                                <div class="document-card-insight">
                                    <span class="document-card-insight-label">วันที่เอกสาร</span>
                                    <span class="document-card-insight-value <?= $documentDateDisplay === '-' ? 'is-muted' : '' ?>"><?= htmlspecialchars($documentDateDisplay) ?></span>
                                </div>
                                <div class="document-card-insight">
                                    <span class="document-card-insight-label">บันทึกเมื่อ</span>
                                    <span class="document-card-insight-value"><?= htmlspecialchars($createdAtDisplay) ?></span>
                                </div>
                                <div class="document-card-insight">
                                    <span class="document-card-insight-label">ไฟล์ PDF</span>
                                    <span class="document-card-insight-value <?= empty($item['pdf_file']) ? 'is-muted' : '' ?>"><?= htmlspecialchars($pdfStatusLabel) ?></span>
                                </div>
                                <div class="document-card-insight">
                                    <span class="document-card-insight-label">ภาพปก</span>
                                    <span class="document-card-insight-value <?= empty($item['image']) ? 'is-muted' : '' ?>"><?= htmlspecialchars($coverStatusLabel) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="document-card-media">
                            <?php if ($item['image']): ?>
                                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="ภาพตัวอย่างเอกสาร <?= htmlspecialchars($item['name']) ?>" class="image-thumbnail" tabindex="0" role="button" data-full-image="uploads/<?= htmlspecialchars($item['image']) ?>" onclick="openModal('uploads/<?= htmlspecialchars($item['image']) ?>')">
                            <?php else: ?>
                                <div class="document-card-media-placeholder">
                                    <i class="bi bi-file-earmark-text"></i>
                                    <span>Document</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="document-card-actions-primary">
                        <?php if ($item['pdf_file']): ?>
                            <a href="uploads/<?= htmlspecialchars($item['pdf_file']) ?>" target="_blank" class="btn btn-primary btn-action-main"><i class="bi bi-eye"></i> เปิดอ่านเอกสาร</a>
                        <?php else: ?>
                            <span class="document-card-status"><i class="bi bi-info-circle"></i> ยังไม่มีไฟล์ PDF สำหรับรายการนี้</span>
                        <?php endif; ?>
                    </div>

                    <div class="document-card-footer">
                        <div class="document-card-footer-meta-group">
                            <div class="document-card-footer-meta"><?= $documentMetaText ?></div>
                            <div class="document-card-footer-meta">
                                <?= !empty($item['pdf_file']) ? 'PDF พร้อมใช้งาน' : 'ยังไม่มีไฟล์ PDF' ?>
                            </div>
                        </div>
                        <div class="document-card-actions-secondary">
                            <?php if ($item['pdf_file']): ?>
                                <a href="uploads/<?= htmlspecialchars($item['pdf_file']) ?>" download class="btn btn-soft"><i class="bi bi-download"></i> ดาวน์โหลด</a>
                            <?php endif; ?>
                            <?php if (!isGuest()): ?>
                                <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-soft"><i class="bi bi-pencil-square"></i> แก้ไข</a>
                                <a href="delete.php?id=<?= $item['id'] ?>" class="btn btn-soft btn-soft-danger" onclick="return confirm('คุณต้องการลบข้อมูลนี้หรือไม่?')"><i class="bi bi-trash"></i> ลบ</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="document-empty">
                <div class="document-empty-icon"><i class="bi bi-journal-bookmark"></i></div>
                <div class="document-empty-title">ไม่พบเอกสารตามเงื่อนไขที่เลือก</div>
                <div class="document-empty-text">ลองเปลี่ยนคำค้นหา หมวดกฎหมาย ปีงบประมาณ หรือกลับไปดูรายการทั้งหมด</div>
                <div class="document-empty-actions">
                    <a href="<?= htmlspecialchars($buildEbookUrl(['search' => null, 'search_field' => 'all', 'category_id' => null, 'subcategory_id' => null, 'fiscal_year' => null, 'sort' => 'latest', 'page' => null])) ?>" class="btn btn-primary"><i class="bi bi-grid"></i> ดูทั้งหมด</a>
                    <a href="<?= htmlspecialchars($buildEbookUrl(['search' => null, 'search_field' => 'all', 'category_id' => null, 'subcategory_id' => null, 'fiscal_year' => null, 'sort' => $sort, 'page' => null])) ?>" class="btn btn-secondary"><i class="bi bi-arrow-counterclockwise"></i> ล้างตัวกรอง</a>
                </div>
            </div>
            <?php endif; ?>

        <?php if ($totalPages > 1): ?>
        <nav aria-label="Pagination">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link page-link-nav" href="<?= htmlspecialchars($buildEbookUrl(['page' => 1])) ?>" aria-label="หน้าแรก">
                        <i class="bi bi-chevron-double-left"></i>
                        <span class="page-link-label">หน้าแรก</span>
                    </a>
                </li>
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link page-link-nav" href="<?= htmlspecialchars($buildEbookUrl(['page' => max(1, $page - 1)])) ?>" aria-label="หน้าก่อนหน้า">
                        <i class="bi bi-chevron-left"></i>
                        <span class="page-link-label">ก่อนหน้า</span>
                    </a>
                </li>
                <?php foreach ($paginationItems as $paginationItem): ?>
                    <?php if ($paginationItem === null): ?>
                        <li class="page-item page-item-ellipsis disabled"><span class="page-link">...</span></li>
                    <?php else: ?>
                        <li class="page-item <?= ((int) $paginationItem === $page) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($buildEbookUrl(['page' => (int) $paginationItem])) ?>"><?= (int) $paginationItem ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link page-link-nav" href="<?= htmlspecialchars($buildEbookUrl(['page' => min($totalPages, $page + 1)])) ?>" aria-label="หน้าถัดไป">
                        <span class="page-link-label">ถัดไป</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link page-link-nav" href="<?= htmlspecialchars($buildEbookUrl(['page' => $totalPages])) ?>" aria-label="หน้าสุดท้าย">
                        <span class="page-link-label">หน้าสุดท้าย</span>
                        <i class="bi bi-chevron-double-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>

        <p class="text-end records-summary">จำนวนข้อมูลทั้งหมด: <?= $totalItems ?> รายการ</p>
    </div>

    <div id="imageModal" class="modal">
        <span class="close">×</span>
        <img class="modal-content" id="fullImage">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        if (typeof window.particlesJS === "function") {
            window.particlesJS("particles-js", {
                particles: {
                    number: { value: 60, density: { enable: true, value_area: 800 } },
                    color: { value: "#8B5CF6" },
                    shape: { type: "circle" },
                    opacity: { value: 0.4, random: true },
                    size: { value: 3, random: true },
                    line_linked: { enable: true, distance: 150, color: "#A78BFA", opacity: 0.3, width: 1 },
                    move: { enable: true, speed: 2, direction: "none", random: false, straight: false, out_mode: "out" }
                },
                interactivity: {
                    detect_on: "canvas",
                    events: {
                        onhover: { enable: true, mode: "repulse" },
                        onclick: { enable: true, mode: "push" },
                        resize: true
                    },
                    modes: {
                        repulse: { distance: 100, duration: 0.4 },
                        push: { particles_nb: 4 }
                    }
                },
                retina_detect: true
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            const cards = document.querySelectorAll(".document-card");
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add("visible");
                }, index * 100);
            });

            const filterForm = document.getElementById("ebookFiltersForm");
            const advancedFilters = document.getElementById("advancedFilters");
            const toggleAdvancedFilters = document.getElementById("toggleAdvancedFilters");
            const categoryFilter = filterForm ? filterForm.querySelector('select[name="category_id"]') : null;
            const subcategoryFilter = document.getElementById("subcategoryFilter");
            const autoSubmitSelects = filterForm
                ? filterForm.querySelectorAll('select[name="fiscal_year"], select[name="sort"]')
                : [];
            const hasPersistentAdvancedFilters = <?= $hasAdvancedFilters ? 'true' : 'false' ?>;
            const subcategoryOptions = <?= json_encode($subcategoryFilterOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const articleFilterCounts = <?= json_encode($articleFilterCounts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const articleFilterOrder = <?= json_encode($articleFilterCategoryOrder, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const articleFilterTitles = <?= json_encode($articleFilterTitles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

            function hydrateArticleFilterCounts() {
                const articleLinks = document.querySelectorAll(".article-filter-bar .article-filter-link");
                articleLinks.forEach((link, index) => {
                    if (link.querySelector(".article-filter-link-label")) {
                        return;
                    }

                    const currentText = link.textContent.trim();
                    const label = document.createElement("span");
                    const countKey = index === 0 ? "all" : String(articleFilterOrder[index - 1] ?? "");

                    label.className = "article-filter-link-label";
                    label.textContent = currentText;

                    link.textContent = "";
                    link.append(label);

                    if (index === 0) {
                        link.classList.add("article-filter-link-all");
                        const count = document.createElement("span");
                        count.className = "article-filter-link-count";
                        count.textContent = String(articleFilterCounts[countKey] ?? 0);
                        link.append(count);
                        return;
                    }

                    const detailText = String(articleFilterTitles[countKey] ?? "").trim();
                    if (detailText !== "") {
                        const detail = document.createElement("span");
                        detail.className = "article-filter-link-detail";
                        detail.textContent = detailText;
                        link.append(detail);
                    }
                });
            }

            function renderSubcategoryOptions(categoryValue, selectedValue) {
                if (!subcategoryFilter) {
                    return;
                }

                const normalizedCategoryValue = String(categoryValue || "");
                const nextOptions = normalizedCategoryValue !== "" ? (subcategoryOptions[normalizedCategoryValue] || []) : [];
                const canSelectSubcategory = nextOptions.length > 0;

                subcategoryFilter.innerHTML = "";

                const placeholderOption = document.createElement("option");
                placeholderOption.value = "";
                placeholderOption.textContent = canSelectSubcategory ? "ทุกรายละเอียดย่อย" : "เลือกมาตราก่อน";
                subcategoryFilter.appendChild(placeholderOption);

                nextOptions.forEach((optionItem) => {
                    const option = document.createElement("option");
                    option.value = String(optionItem.id);
                    option.textContent = `${optionItem.label} (${optionItem.count})`;
                    subcategoryFilter.appendChild(option);
                });

                subcategoryFilter.disabled = !canSelectSubcategory;
                if (canSelectSubcategory && selectedValue && nextOptions.some((optionItem) => String(optionItem.id) === String(selectedValue))) {
                    subcategoryFilter.value = String(selectedValue);
                } else {
                    subcategoryFilter.value = "";
                }
            }

            function setAdvancedFiltersState(isOpen) {
                if (!advancedFilters) {
                    return;
                }

                advancedFilters.classList.toggle("is-open", isOpen);

                if (toggleAdvancedFilters) {
                    toggleAdvancedFilters.setAttribute("aria-expanded", isOpen ? "true" : "false");
                }
            }

            if (advancedFilters) {
                if (window.innerWidth >= 992) {
                    setAdvancedFiltersState(true);
                } else {
                    setAdvancedFiltersState(hasPersistentAdvancedFilters);
                }
            }

            hydrateArticleFilterCounts();
            renderSubcategoryOptions(categoryFilter ? categoryFilter.value : "", "<?= (int) $subcategoryId ?>");

            if (advancedFilters && toggleAdvancedFilters) {
                toggleAdvancedFilters.addEventListener("click", function () {
                    setAdvancedFiltersState(!advancedFilters.classList.contains("is-open"));
                });
            }

            if (categoryFilter) {
                categoryFilter.addEventListener("change", function () {
                    renderSubcategoryOptions(categoryFilter.value, "");
                });
            }

            autoSubmitSelects.forEach((selectElement) => {
                selectElement.addEventListener("change", function () {
                    if (filterForm) {
                        filterForm.requestSubmit();
                    }
                });
            });

            const descriptionToggles = document.querySelectorAll("[data-description-toggle]");
            descriptionToggles.forEach((toggleButton) => {
                toggleButton.addEventListener("click", function () {
                    const targetId = toggleButton.getAttribute("data-description-toggle");
                    if (!targetId) {
                        return;
                    }

                    const target = document.getElementById(targetId);
                    if (!target) {
                        return;
                    }

                    const isExpanded = !target.classList.contains("is-collapsed");
                    target.classList.toggle("is-collapsed", isExpanded);
                    toggleButton.setAttribute("aria-expanded", isExpanded ? "false" : "true");
                    toggleButton.textContent = isExpanded
                        ? (toggleButton.getAttribute("data-more-label") || "อ่านรายละเอียดเพิ่ม")
                        : (toggleButton.getAttribute("data-less-label") || "ซ่อนรายละเอียด");
                });
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
                if (popupOverlay) {
                    popupOverlay.classList.remove("show");
                }
            }

            if (openPopup && popupOverlay) {
                openPopup.addEventListener("click", function (event) {
                    event.preventDefault();
                    popupOverlay.classList.add("show");
                });
            }

            if (closePopup) {
                closePopup.addEventListener("click", closePopupFunc);
            }

            if (closePopupBtn) {
                closePopupBtn.addEventListener("click", closePopupFunc);
            }

            if (popupOverlay) {
                popupOverlay.addEventListener("click", function (event) {
                    if (event.target === popupOverlay) {
                        closePopupFunc();
                    }
                });
            }

            const modal = document.getElementById("imageModal");
            const fullImage = document.getElementById("fullImage");
            const closeButton = document.querySelector(".close");

            if (closeButton) {
                closeButton.textContent = "×";
            }

            function openModal(src) {
                if (!src || !modal || !fullImage) {
                    return;
                }

                fullImage.src = src;
                fullImage.style.width = "";
                fullImage.style.height = "";
                modal.style.display = "flex";
            }

            function closeModal() {
                if (modal) {
                    modal.style.display = "none";
                }
            }

            if (closeButton) {
                closeButton.textContent = "\u00D7";
                closeButton.addEventListener("click", closeModal);
            }

            if (modal) {
                modal.addEventListener("click", function (event) {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
            }

            const focusableImages = document.querySelectorAll(".image-thumbnail[tabindex]");
            focusableImages.forEach((image) => {
                image.addEventListener("keydown", function (event) {
                    if (event.key === "Enter" || event.key === " ") {
                        event.preventDefault();
                        openModal(image.dataset.fullImage || image.getAttribute("src"));
                    }
                });
            });

            document.addEventListener("keydown", function (event) {
                if (event.key === "Escape") {
                    if (sidebar) {
                        sidebar.classList.remove("active");
                    }
                    if (advancedFilters && window.innerWidth < 992) {
                        setAdvancedFiltersState(false);
                    }
                    closePopupFunc();
                    closeModal();
                }
            });

            window.openModal = openModal;

            window.addEventListener("orientationchange", function () {
                if (sidebar) {
                    sidebar.classList.remove("active");
                }
                if (advancedFilters && window.innerWidth < 992 && !hasPersistentAdvancedFilters) {
                    setAdvancedFiltersState(false);
                }
            });

            window.addEventListener("resize", function () {
                if (sidebar && window.innerWidth > 992) {
                    sidebar.classList.remove("active");
                }
                if (advancedFilters && window.innerWidth >= 992) {
                    setAdvancedFiltersState(true);
                } else if (advancedFilters && window.innerWidth < 992 && !hasPersistentAdvancedFilters) {
                    setAdvancedFiltersState(false);
                }
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
