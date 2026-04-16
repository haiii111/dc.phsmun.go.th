<?php
declare(strict_types=1);

$scanDir = realpath(__DIR__);
$projectRoot = realpath(__DIR__);

if ($scanDir === false || $projectRoot === false || !is_dir($scanDir)) {
    http_response_code(500);
    exit('ไม่พบโฟลเดอร์สำหรับสแกน');
}

$excludedDirs = [
    '.git',
    '.idea',
    '.vscode',
    'vendor',
    'node_modules',
    'cache',
    'tmp',
    'logs',
];

$scanExtensions = ['php', 'phtml', 'php5', 'php7', 'phar', 'inc'];
$maxFileSize = 2 * 1024 * 1024;
$maxPreviewLength = 180;
$maxFindings = 300;

$patternRules = [
    [
        'severity' => 'critical',
        'title' => 'รันโค้ดจาก input ตรง ๆ',
        'regex' => '/eval\s*\(.{0,120}\$_(POST|GET|REQUEST|COOKIE|SERVER)/i',
        'description' => 'รูปแบบนี้มักใช้ใน webshell หรือ remote code execution',
        'recommendation' => 'ห้าม eval input จากผู้ใช้โดยตรง ให้ใช้ allowlist และ parse ข้อมูลแทน',
    ],
    [
        'severity' => 'critical',
        'title' => 'assert รับข้อมูลจากภายนอก',
        'regex' => '/assert\s*\(.{0,120}\$_(POST|GET|REQUEST|COOKIE|SERVER)/i',
        'description' => 'assert กับ input ภายนอกเสี่ยงสั่งรันคำสั่ง',
        'recommendation' => 'ลบการใช้ assert กับ input ภายนอกทั้งหมด',
    ],
    [
        'severity' => 'critical',
        'title' => 'เรียกคำสั่งระบบจาก input',
        'regex' => '/(shell_exec|system|passthru|exec|popen|proc_open)\s*\(.{0,120}\$_(POST|GET|REQUEST|COOKIE|SERVER)/i',
        'description' => 'เสี่ยง command injection โดยตรง',
        'recommendation' => 'ห้ามส่งค่าจากผู้ใช้เข้า shell โดยตรง และใช้ API ภายในแทน',
    ],
    [
        'severity' => 'high',
        'title' => 'ถอดรหัส payload จาก input',
        'regex' => '/base64_decode\s*\(.{0,120}\$_(POST|GET|REQUEST|COOKIE|SERVER)/i',
        'description' => 'มักใช้ซ่อน payload อันตรายก่อนนำไปประมวลผล',
        'recommendation' => 'ตรวจรูปแบบข้อมูลก่อน decode และอย่านำผลลัพธ์ไปรันเป็นโค้ด',
    ],
    [
        'severity' => 'high',
        'title' => 'include/require จาก input',
        'regex' => '/(include|include_once|require|require_once)\s*\(?\s*\$_(POST|GET|REQUEST|COOKIE|SERVER)/i',
        'description' => 'เสี่ยง local file inclusion หรือ remote file inclusion',
        'recommendation' => 'เปลี่ยนเป็น map ชื่อไฟล์แบบ allowlist เท่านั้น',
    ],
    [
        'severity' => 'high',
        'title' => 'อัปโหลดไฟล์โดยยังไม่เห็นการจำกัดชนิด',
        'regex' => '/move_uploaded_file\s*\(/i',
        'description' => 'ถ้าไม่มี allowlist นามสกุลและ MIME type อาจอัปโหลดสคริปต์ได้',
        'recommendation' => 'ตรวจ MIME type, นามสกุล, randomize filename และเก็บนอก web root',
    ],
    [
        'severity' => 'high',
        'title' => 'decode chain ที่นิยมใน webshell',
        'regex' => '/(gzinflate|gzuncompress|str_rot13|base64_decode)\s*\(/i',
        'description' => 'ควรตรวจเพิ่มถ้าพบร่วมกับ eval หรือ include แบบ dynamic',
        'recommendation' => 'ตรวจสอบเจตนาของโค้ดช่วงนั้นว่าจำเป็นจริงหรือไม่',
    ],
    [
        'severity' => 'medium',
        'title' => 'ซ่อน error ด้วย @',
        'regex' => '/@\s*(eval|include|require|file_get_contents|fopen|unlink|system|exec|shell_exec)\s*\(/i',
        'description' => 'การซ่อน error ทำให้ซ่อนพฤติกรรมผิดปกติได้ง่าย',
        'recommendation' => 'เอา @ ออกและจัดการ exception หรือ error ให้ชัดเจน',
    ],
    [
        'severity' => 'medium',
        'title' => 'สุ่มค่าด้วยฟังก์ชันไม่เหมาะกับ security',
        'regex' => '/\b(mt_rand|rand|uniqid)\s*\(/i',
        'description' => 'ไม่ควรใช้สร้าง token, otp, reset link หรือ secret',
        'recommendation' => 'ใช้ random_int() หรือ bin2hex(random_bytes())',
    ],
    [
        'severity' => 'medium',
        'title' => 'อาจมี secret ฝังในซอร์ส',
        'regex' => '/\b(api[_-]?key|secret|token|password)\b.{0,30}[\'"][^\'"]{8,}[\'"]/i',
        'description' => 'ข้อมูลลับในซอร์สเสี่ยงรั่วผ่าน git หรือ backup',
        'recommendation' => 'ย้าย secret ไปไว้ environment variable หรือไฟล์ config นอก web root',
    ],
];

$dangerousExtensions = ['php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8'];
$uploadDirHints = ['upload', 'uploads', 'file', 'files', 'pic', 'image', 'img', 'doc', 'edoc'];
$suspiciousNames = '/(shell|cmd|backdoor|webshell|r57|c99|wso|b374k|minishell|upload|uploader)/i';

$severityOrder = ['critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 4];
$severityColors = [
    'critical' => '#b42318',
    'high' => '#d97706',
    'medium' => '#2563eb',
    'low' => '#475467',
];

$summary = [
    'directories_scanned' => 0,
    'files_scanned' => 0,
    'files_skipped' => 0,
    'read_errors' => 0,
];

$findings = [];
$securityNotes = [];

function addFinding(array &$findings, array &$summary, array $finding, int $maxFindings): void
{
    if (count($findings) >= $maxFindings) {
        $summary['files_skipped']++;
        return;
    }

    $findings[] = $finding;
}

function relativePath(string $path, string $root): string
{
    if (strpos($path, $root) === 0) {
        $trimmed = ltrim(substr($path, strlen($root)), DIRECTORY_SEPARATOR);
        return $trimmed !== '' ? $trimmed : '.';
    }

    return $path;
}

function isPathInside(string $path, string $root): bool
{
    $normalizedPath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
    $normalizedRoot = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR);

    return $normalizedPath === $normalizedRoot
        || strpos($normalizedPath . DIRECTORY_SEPARATOR, $normalizedRoot . DIRECTORY_SEPARATOR) === 0;
}

function isExcludedDirectory(string $path, array $excludedDirs): bool
{
    $segments = preg_split('/[\\\\\\/]+/', $path) ?: [];
    foreach ($segments as $segment) {
        if (in_array($segment, $excludedDirs, true)) {
            return true;
        }
    }

    return false;
}

function isUploadLikePath(string $relativePath, array $uploadDirHints): bool
{
    $lower = strtolower($relativePath);
    foreach ($uploadDirHints as $hint) {
        if (strpos($lower, $hint) !== false) {
            return true;
        }
    }

    return false;
}

function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $size = $bytes;
    $index = 0;

    while ($size >= 1024 && $index < count($units) - 1) {
        $size /= 1024;
        $index++;
    }

    return number_format($size, $index === 0 ? 0 : 2) . ' ' . $units[$index];
}

function severityBadge(string $severity, array $severityColors): string
{
    $color = $severityColors[$severity] ?? '#475467';
    return '<span style="display:inline-block;padding:3px 8px;border-radius:999px;font-size:12px;font-weight:700;color:#fff;background:' . $color . ';">'
        . htmlspecialchars(strtoupper($severity), ENT_QUOTES, 'UTF-8')
        . '</span>';
}

function previewText(string $text, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $length);
    }

    return substr($text, 0, $length);
}

$directoryIterator = new RecursiveDirectoryIterator($scanDir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO);
$filteredIterator = new RecursiveCallbackFilterIterator(
    $directoryIterator,
    static function (SplFileInfo $current) use ($projectRoot, $excludedDirs): bool {
        $realPath = $current->getRealPath();
        if ($realPath === false || !isPathInside($realPath, $projectRoot)) {
            return false;
        }

        $relative = relativePath($realPath, $projectRoot);
        if ($current->isDir() && isExcludedDirectory($relative, $excludedDirs)) {
            return false;
        }

        return true;
    }
);

$iterator = new RecursiveIteratorIterator(
    $filteredIterator,
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $realPath = $item->getRealPath();
    if ($realPath === false || !isPathInside($realPath, $projectRoot)) {
        continue;
    }

    $relative = relativePath($realPath, $projectRoot);

    if ($item->isDir()) {
        if (!isExcludedDirectory($relative, $excludedDirs)) {
            $summary['directories_scanned']++;
        }
        continue;
    }

    if (!$item->isFile() || isExcludedDirectory($relative, $excludedDirs)) {
        continue;
    }

    $summary['files_scanned']++;
    $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
    $filename = basename($realPath);
    $fileSize = (int) $item->getSize();

    if (preg_match($suspiciousNames, $filename)) {
        addFinding($findings, $summary, [
            'severity' => 'medium',
            'file' => $relative,
            'line' => '-',
            'title' => 'ชื่อไฟล์น่าสงสัย',
            'snippet' => $filename,
            'description' => 'ชื่อไฟล์เข้ากับรูปแบบที่พบบ่อยใน backdoor หรือ upload shell',
            'recommendation' => 'ตรวจสอบที่มาของไฟล์และลบถ้าไม่ใช่ไฟล์ที่ระบบต้องใช้',
        ], $maxFindings);
    }

    if (isUploadLikePath($relative, $uploadDirHints) && in_array($extension, $dangerousExtensions, true)) {
        addFinding($findings, $summary, [
            'severity' => 'critical',
            'file' => $relative,
            'line' => '-',
            'title' => 'พบสคริปต์ในโฟลเดอร์อัปโหลด',
            'snippet' => $filename,
            'description' => 'ไฟล์สคริปต์อยู่ในตำแหน่งที่มักใช้เก็บไฟล์จากผู้ใช้',
            'recommendation' => 'ย้ายไฟล์อัปโหลดออกนอก web root และบล็อกการรัน PHP ในโฟลเดอร์อัปโหลด',
        ], $maxFindings);
    }

    if ($fileSize > $maxFileSize) {
        $summary['files_skipped']++;
        continue;
    }

    if (!in_array($extension, $scanExtensions, true)) {
        continue;
    }

    if (!is_readable($realPath)) {
        $summary['read_errors']++;
        addFinding($findings, $summary, [
            'severity' => 'low',
            'file' => $relative,
            'line' => '-',
            'title' => 'อ่านไฟล์ไม่ได้',
            'snippet' => '',
            'description' => 'ไฟล์นี้อ่านไม่ได้ จึงตรวจสอบซอร์สภายในไม่ได้',
            'recommendation' => 'ตรวจ permission และสแกนซ้ำอีกครั้ง',
        ], $maxFindings);
        continue;
    }

    $lines = @file($realPath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        $summary['read_errors']++;
        continue;
    }

    foreach ($lines as $lineNumber => $line) {
        foreach ($patternRules as $rule) {
            if (!preg_match($rule['regex'], $line)) {
                continue;
            }

            addFinding($findings, $summary, [
                'severity' => $rule['severity'],
                'file' => $relative,
                'line' => (string) ($lineNumber + 1),
                'title' => $rule['title'],
                'snippet' => previewText(trim($line), $maxPreviewLength),
                'description' => $rule['description'],
                'recommendation' => $rule['recommendation'],
            ], $maxFindings);
        }
    }
}

foreach (['config', 'include', 'api', 'assets', 'pic_file', 'edoc_file'] as $dirName) {
    $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $dirName;
    if (is_dir($fullPath) && is_writable($fullPath)) {
        $securityNotes[] = [
            'severity' => 'medium',
            'message' => 'โฟลเดอร์ `' . $dirName . '` เขียนได้โดย PHP/เว็บเซิร์ฟเวอร์',
            'recommendation' => 'ให้สิทธิ์ write เฉพาะโฟลเดอร์ที่จำเป็นจริง และแยก upload ออกจากไฟล์โปรแกรม',
        ];
    }
}

foreach (['.env', '.git', '.gitignore', 'composer.json', 'composer.lock'] as $sensitiveName) {
    $fullPath = $projectRoot . DIRECTORY_SEPARATOR . $sensitiveName;
    if (file_exists($fullPath)) {
        $securityNotes[] = [
            'severity' => in_array($sensitiveName, ['.env', '.git'], true) ? 'high' : 'low',
            'message' => 'พบไฟล์หรือโฟลเดอร์ที่ไม่ควรถูกเปิดอ่านจากเว็บ: `' . $sensitiveName . '`',
            'recommendation' => 'บล็อกการเข้าถึงผ่าน web server หรือย้ายออกนอก public web root',
        ];
    }
}

usort($findings, static function (array $a, array $b) use ($severityOrder): int {
    $severityCompare = ($severityOrder[$a['severity']] ?? 99) <=> ($severityOrder[$b['severity']] ?? 99);
    if ($severityCompare !== 0) {
        return $severityCompare;
    }

    return strcmp($a['file'], $b['file']);
});

$severityCounts = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
foreach ($findings as $finding) {
    $severityCounts[$finding['severity']]++;
}
foreach ($securityNotes as $note) {
    $severityCounts[$note['severity']]++;
}

?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Security Scan Report</title>
    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #475467;
            --line: #d0d5dd;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Tahoma, Arial, sans-serif;
            background: linear-gradient(180deg, #eef6ff 0%, #f8fafc 100%);
            color: var(--text);
        }
        .wrap {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px;
        }
        .panel {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 18px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
        }
        h1, h2 { margin-top: 0; }
        p { color: var(--muted); line-height: 1.6; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        .stat {
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
        }
        .stat strong {
            display: block;
            font-size: 24px;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            vertical-align: top;
            border-top: 1px solid var(--line);
            padding: 12px 10px;
            font-size: 14px;
        }
        th {
            color: #344054;
            background: #f8fafc;
        }
        code {
            font-family: Consolas, Monaco, monospace;
        }
        .snippet {
            max-width: 620px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .empty {
            padding: 18px;
            border-radius: 14px;
            background: #ecfdf3;
            color: #027a48;
            font-weight: 700;
        }
        ul {
            margin: 0;
            padding-left: 18px;
        }
        .muted {
            color: var(--muted);
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="panel">
        <h1>รายงานสแกนความเสี่ยงด้านความปลอดภัย</h1>
        <p>สคริปต์นี้ตรวจหลายด้านมากกว่าการหา keyword ธรรมดา เช่น webshell, command injection, include จาก input, อัปโหลดไฟล์อันตราย, secret ในซอร์ส และสคริปต์ที่หลุดเข้าโฟลเดอร์อัปโหลด</p>
        <div class="stats">
            <div class="stat">โฟลเดอร์ที่สแกน<strong><?= number_format($summary['directories_scanned']) ?></strong></div>
            <div class="stat">ไฟล์ที่ตรวจ<strong><?= number_format($summary['files_scanned']) ?></strong></div>
            <div class="stat">Critical / High<strong><?= $severityCounts['critical'] . ' / ' . $severityCounts['high'] ?></strong></div>
            <div class="stat">Skipped / Read error<strong><?= $summary['files_skipped'] . ' / ' . $summary['read_errors'] ?></strong></div>
        </div>
        <p class="muted">Root ที่สแกน: <code><?= htmlspecialchars($scanDir, ENT_QUOTES, 'UTF-8') ?></code> | ข้ามโฟลเดอร์: <code><?= htmlspecialchars(implode(', ', $excludedDirs), ENT_QUOTES, 'UTF-8') ?></code> | ขนาดไฟล์สูงสุด: <code><?= htmlspecialchars(formatBytes($maxFileSize), ENT_QUOTES, 'UTF-8') ?></code></p>
    </div>

    <div class="panel">
        <h2>สิ่งที่เพิ่มขึ้นจากเวอร์ชันเดิม</h2>
        <ul>
            <li>ล็อกขอบเขตการสแกนให้อยู่ใน project root เพื่อกัน path ผิดหรือ traversal</li>
            <li>ข้ามโฟลเดอร์ที่ไม่จำเป็นเพื่อลด false positive และลดเวลาในการสแกน</li>
            <li>ไม่พยายามอ่านไฟล์ใหญ่เกินกำหนด ลดโอกาสกิน memory มากเกินไป</li>
            <li>ตรวจ pattern ที่สะท้อนความเสี่ยงจริงมากขึ้น ไม่ใช่แค่มีคำว่า <code>eval</code> อย่างเดียว</li>
            <li>ตรวจไฟล์สคริปต์ในโฟลเดอร์อัปโหลดและชื่อไฟล์ที่คล้าย webshell</li>
            <li>สรุปผลเป็นระดับความรุนแรงพร้อมคำแนะนำการแก้ไข</li>
        </ul>
    </div>

    <div class="panel">
        <h2>ข้อสังเกตด้านโครงสร้างระบบ</h2>
        <?php if ($securityNotes === []): ?>
            <div class="empty">ยังไม่พบข้อสังเกตเชิงโครงสร้างที่เด่นชัดจาก permission และไฟล์อ่อนไหว</div>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ระดับ</th>
                    <th>ประเด็น</th>
                    <th>คำแนะนำ</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($securityNotes as $note): ?>
                    <tr>
                        <td><?= severityBadge($note['severity'], $severityColors) ?></td>
                        <td><?= htmlspecialchars($note['message'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($note['recommendation'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h2>ผลการตรวจโค้ดต้องสงสัย</h2>
        <?php if ($findings === []): ?>
            <div class="empty">ยังไม่พบรูปแบบเสี่ยงตามกฎที่กำหนดในไฟล์ที่ตรวจได้</div>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ระดับ</th>
                    <th>ไฟล์</th>
                    <th>บรรทัด</th>
                    <th>ประเด็น</th>
                    <th>รายละเอียด</th>
                    <th>คำแนะนำ</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($findings as $finding): ?>
                    <tr>
                        <td><?= severityBadge($finding['severity'], $severityColors) ?></td>
                        <td><code><?= htmlspecialchars($finding['file'], ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td><?= htmlspecialchars($finding['line'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <strong><?= htmlspecialchars($finding['title'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                            <span class="muted"><?= htmlspecialchars($finding['description'], ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td class="snippet"><code><?= htmlspecialchars($finding['snippet'], ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td><?= htmlspecialchars($finding['recommendation'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
