<?php
$target_url = '';
$results = [];
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    
    if (filter_var($target_url, FILTER_VALIDATE_URL)) {
        // ใช้ get_headers เพื่อดึงข้อมูล Header จาก URL เป้าหมาย
        stream_context_set_default([
            'http' => ['method' => 'HEAD', 'timeout' => 5]
        ]);
        
        $headers = @get_headers($target_url, 1);
        
        if ($headers) {
            // แปลง key ของ header ให้เป็นตัวพิมพ์เล็กทั้งหมดเพื่อง่ายต่อการตรวจสอบ
            $headers = array_change_key_case($headers, CASE_LOWER);

            // 1. ตรวจสอบ X-Frame-Options (ป้องกัน Clickjacking)
            $results['X-Frame-Options'] = isset($headers['x-frame-options']) 
                ? ["status" => "ปลอดภัย", "msg" => "พบค่า: " . (is_array($headers['x-frame-options']) ? $headers['x-frame-options'][0] : $headers['x-frame-options'])] 
                : ["status" => "เสี่ยง", "msg" => "ไม่พบ Header นี้ (อาจถูกโจมตีแบบ Clickjacking ได้)"];

            // 2. ตรวจสอบ X-Content-Type-Options (ป้องกัน MIME-sniffing)
            $results['X-Content-Type-Options'] = isset($headers['x-content-type-options']) 
                ? ["status" => "ปลอดภัย", "msg" => "พบค่า: " . (is_array($headers['x-content-type-options']) ? $headers['x-content-type-options'][0] : $headers['x-content-type-options'])] 
                : ["status" => "เสี่ยง", "msg" => "ไม่พบ Header นี้ (เบราว์เซอร์อาจคาดเดาประเภทไฟล์ผิดพลาด)"];

            // 3. ตรวจสอบ Strict-Transport-Security (บังคับใช้ HTTPS)
            $results['Strict-Transport-Security'] = isset($headers['strict-transport-security']) 
                ? ["status" => "ปลอดภัย", "msg" => "พบการบังคับใช้ HTTPS"] 
                : ["status" => "เสี่ยง", "msg" => "ไม่พบ HSTS (ข้อมูลอาจถูกดักจับบนเครือข่ายที่ไม่ปลอดภัย)"];
                
            // 4. ตรวจสอบ Server Signature (การเปิดเผยเวอร์ชันของเซิร์ฟเวอร์)
            $results['Server Signature'] = isset($headers['server']) 
                ? ["status" => "คำเตือน", "msg" => "เซิร์ฟเวอร์เปิดเผยเวอร์ชัน: " . (is_array($headers['server']) ? $headers['server'][0] : $headers['server'])] 
                : ["status" => "ปลอดภัย", "msg" => "ซ่อนข้อมูลเซิร์ฟเวอร์แล้ว"];

        } else {
            $error = "ไม่สามารถเชื่อมต่อกับ URL นี้ได้ หรือ URL ไม่ถูกต้อง";
        }
    } else {
        $error = "รูปแบบ URL ไม่ถูกต้อง (กรุณาระบุ http:// หรือ https://)";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Web Security Checker</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        input[type="text"] { width: 70%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #ecf0f1; }
        .safe { color: #27ae60; font-weight: bold; }
        .risk { color: #e74c3c; font-weight: bold; }
        .warn { color: #f39c12; font-weight: bold; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>ระบบตรวจสอบ Security Headers เบื้องต้น</h2>
        <form method="POST" action="">
            <input type="text" name="url" placeholder="ตัวอย่าง: https://www.example.com" value="<?php echo htmlspecialchars($target_url); ?>" required>
            <button type="submit">เริ่มสแกน</button>
        </form>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>รายการตรวจสอบ</th>
                        <th>สถานะ</th>
                        <th>รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $check => $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($check); ?></td>
                            <td class="<?php 
                                if($data['status'] == 'ปลอดภัย') echo 'safe'; 
                                elseif($data['status'] == 'เสี่ยง') echo 'risk'; 
                                else echo 'warn'; 
                            ?>">
                                <?php echo $data['status']; ?>
                            </td>
                            <td><?php echo htmlspecialchars($data['msg']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>