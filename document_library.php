<?php

function document_category_seed_rows(): array
{
    return [
        [
            'code' => 'article-7',
            'reference_label' => 'มาตรา 7',
            'title' => 'ประกาศในราชกิจจานุเบกษาและข้อมูลพื้นฐานของหน่วยงาน',
            'description' => 'โครงสร้างองค์กร อำนาจหน้าที่ วิธีดำเนินงาน และสถานที่ติดต่อเพื่อขอรับข้อมูลข่าวสาร',
            'sort_order' => 10,
        ],
        [
            'code' => 'article-9-1',
            'reference_label' => 'มาตรา 9(1)',
            'title' => 'ผลการพิจารณาหรือคำวินิจฉัยที่มีผลโดยตรงต่อเอกชน',
            'description' => 'เช่น ผลการพิจารณาอนุญาตก่อสร้าง ดัดแปลง รื้อถอน หรือเคลื่อนย้ายอาคาร',
            'sort_order' => 20,
        ],
        [
            'code' => 'article-9-2',
            'reference_label' => 'มาตรา 9(2)',
            'title' => 'นโยบายหรือการตีความที่ไม่เข้าข่ายมาตรา 7(4)',
            'description' => 'เช่น คำแถลงนโยบายของผู้บริหารท้องถิ่นต่อสภาท้องถิ่นก่อนเข้าปฏิบัติหน้าที่',
            'sort_order' => 30,
        ],
        [
            'code' => 'article-9-3',
            'reference_label' => 'มาตรา 9(3)',
            'title' => 'แผนงาน โครงการ และงบประมาณรายจ่ายประจำปี',
            'description' => 'เช่น แผนการดำเนินงาน โครงการที่อนุมัติแล้ว และเทศบัญญัติหรือข้อบัญญัติงบประมาณ',
            'sort_order' => 40,
        ],
        [
            'code' => 'article-9-4',
            'reference_label' => 'มาตรา 9(4)',
            'title' => 'คู่มือหรือคำสั่งเกี่ยวกับวิธีปฏิบัติงานของเจ้าหน้าที่',
            'description' => 'คู่มือหรือคำสั่งที่มีผลกระทบถึงสิทธิหน้าที่ของเอกชน',
            'sort_order' => 50,
        ],
        [
            'code' => 'article-9-5',
            'reference_label' => 'มาตรา 9(5)',
            'title' => 'สิ่งพิมพ์ที่อ้างอิงตามมาตรา 7 วรรคสอง',
            'description' => 'เอกสารหรือสิ่งพิมพ์ที่กฎหมายกำหนดให้อ้างอิงหรือเปิดเผยเพิ่มเติม',
            'sort_order' => 60,
        ],
        [
            'code' => 'article-9-6',
            'reference_label' => 'มาตรา 9(6)',
            'title' => 'สัญญาสัมปทาน สัญญาผูกขาด หรือสัญญาร่วมทุนกับเอกชน',
            'description' => 'เช่น สัญญากำจัดขยะ สัมปทานเก็บขยะ หรือสัมปทานที่เกี่ยวข้อง',
            'sort_order' => 70,
        ],
        [
            'code' => 'article-9-7',
            'reference_label' => 'มาตรา 9(7)',
            'title' => 'มติคณะรัฐมนตรีหรือมติคณะกรรมการที่แต่งตั้งโดยกฎหมาย',
            'description' => 'รวมถึงมติ ครม. และมติของคณะกรรมการหรือสภาท้องถิ่นที่เกี่ยวข้อง',
            'sort_order' => 80,
        ],
        [
            'code' => 'article-9-8',
            'reference_label' => 'มาตรา 9(8)',
            'title' => 'ข้อมูลข่าวสารอื่น',
            'description' => 'เช่น ผลการจัดซื้อจัดจ้างรายเดือนแบบ สขร.1 และข้อมูลเกณฑ์มาตรฐานความโปร่งใส',
            'sort_order' => 90,
        ],
    ];
}

function document_subcategory_seed_rows(): array
{
    return [
        [
            'category_code' => 'article-7',
            'code' => 'article-7-structure',
            'label' => 'ประกาศเทศบาลฯ/อบต. เรื่องโครงสร้างการจัดองค์กรในการดำเนินงาน สรุปอำนาจหน้าที่ที่สำคัญ วิธีการดำเนินงาน และสถานที่ติดต่อเพื่อขอรับข้อมูลข่าวสาร',
            'description' => 'ข้อมูลพื้นฐานของหน่วยงานตามมาตรา 7 ที่แสดงโครงสร้างองค์กร อำนาจหน้าที่ วิธีดำเนินงาน และช่องทางติดต่อ',
            'keywords' => ['โครงสร้างการจัดองค์กร', 'สรุปอำนาจหน้าที่', 'วิธีการดำเนินงาน', 'สถานที่ติดต่อ', 'ขอรับข้อมูลข่าวสาร'],
            'sort_order' => 10,
        ],
        [
            'category_code' => 'article-7',
            'code' => 'article-7-gazette',
            'label' => 'ข้อบัญญัติองค์การบริหารส่วนตำบล เรื่องกิจการที่เป็นอันตรายต่อสุขภาพ',
            'description' => 'ตัวอย่างกฎ ข้อบัญญัติ หรือประกาศที่กฎหมายกำหนดให้เปิดเผยตามมาตรา 7',
            'keywords' => ['กิจการที่เป็นอันตรายต่อสุขภาพ', 'ข้อบัญญัติองค์การบริหารส่วนตำบล', 'เทศบัญญัติ', 'ข้อบัญญัติ'],
            'sort_order' => 20,
        ],
        [
            'category_code' => 'article-9-1',
            'code' => 'article-9-1-building',
            'label' => 'ผลการพิจารณา/คำวินิจฉัยของ อปท. อนุญาตก่อสร้าง/ดัดแปลง/รื้อถอน/เคลื่อนย้ายอาคาร',
            'description' => 'ผลอนุญาตหรือคำวินิจฉัยที่เกี่ยวข้องกับอาคารซึ่งมีผลโดยตรงต่อเอกชน',
            'keywords' => ['ก่อสร้าง', 'ดัดแปลง', 'รื้อถอน', 'เคลื่อนย้ายอาคาร', 'อนุญาตอาคาร'],
            'sort_order' => 10,
        ],
        [
            'category_code' => 'article-9-1',
            'code' => 'article-9-1-market',
            'label' => 'ผลการพิจารณาใบอนุญาตประกอบกิจการตลาด',
            'description' => 'ผลการพิจารณาเกี่ยวกับการออกหรือแก้ไขใบอนุญาตประกอบกิจการตลาด',
            'keywords' => ['ตลาด', 'ใบอนุญาตตลาด', 'ประกอบกิจการตลาด'],
            'sort_order' => 20,
        ],
        [
            'category_code' => 'article-9-1',
            'code' => 'article-9-1-outside-municipality',
            'label' => 'ผลการพิจารณาการอนุมัติการทำกิจกรรมนอกเขตเทศบาล',
            'description' => 'คำวินิจฉัยหรือผลอนุมัติการดำเนินกิจกรรมที่อยู่นอกเขตเทศบาล',
            'keywords' => ['นอกเขตเทศบาล', 'กิจกรรมนอกเขต', 'อนุมัติการทำกิจกรรม'],
            'sort_order' => 30,
        ],
        [
            'category_code' => 'article-9-2',
            'code' => 'article-9-2-policy-statement',
            'label' => 'คำแถลงนโยบายของนายกเทศมนตรีต่อสภาท้องถิ่น หรือนายกองค์การบริหารส่วนตำบลต่อสภาองค์การบริหารส่วนตำบลก่อนเข้าปฏิบัติหน้าที่',
            'description' => 'เอกสารคำแถลงนโยบายของผู้บริหารท้องถิ่นที่เสนอต่อสภาก่อนเริ่มปฏิบัติหน้าที่',
            'keywords' => ['คำแถลงนโยบาย', 'นายกเทศมนตรี', 'นายกองค์การบริหารส่วนตำบล', 'ก่อนเข้าปฏิบัติหน้าที่'],
            'sort_order' => 10,
        ],
        [
            'category_code' => 'article-9-3',
            'code' => 'article-9-3-development-plan',
            'label' => 'แผนการดำเนินงานประจำปีงบประมาณ พ.ศ. ....',
            'description' => 'แผนการดำเนินงาน แผนพัฒนาท้องถิ่น และเอกสารแผนประจำปีของหน่วยงาน',
            'keywords' => ['แผนการดำเนินงาน', 'แผนการการดำเนินงาน', 'แผนพัฒนาท้องถิ่น', 'ยุทธศาสตร์การพัฒนา', 'แผนปฏิบัติการ', 'ติดตามและประเมินผล'],
            'sort_order' => 10,
        ],
        [
            'category_code' => 'article-9-3',
            'code' => 'article-9-3-approved-project',
            'label' => 'โครงการตามแผนปฏิบัติการประจำปี พ.ศ. .... ที่ได้มีการอนุมัติแล้ว',
            'description' => 'โครงการและเอกสารอนุมัติโครงการตามแผนปฏิบัติการประจำปี',
            'keywords' => ['โครงการตามแผนปฏิบัติการ', 'โครงการที่ได้รับอนุมัติ', 'อนุมัติแล้ว', 'โครงการ'],
            'sort_order' => 20,
        ],
        [
            'category_code' => 'article-9-3',
            'code' => 'article-9-3-budget-adjustment',
            'label' => 'ข้อบัญญัติงบประมาณรายจ่ายประจำปีงบประมาณ พ.ศ. .... (เฉพาะ อบต.)',
            'description' => 'ข้อบัญญัติงบประมาณรายจ่ายประจำปีขององค์การบริหารส่วนตำบล',
            'keywords' => ['ข้อบัญญัติงบประมาณ', 'ข้อบัญญัติ', 'อบต.', 'องค์การบริหารส่วนตำบล'],
            'sort_order' => 30,
        ],
        [
            'category_code' => 'article-9-3',
            'code' => 'article-9-3-budget-ordinance',
            'label' => 'เทศบัญญัติงบประมาณรายจ่ายประจำปีงบประมาณ พ.ศ. .... (เฉพาะเทศบาล)',
            'description' => 'เทศบัญญัติงบประมาณรายจ่ายประจำปีของเทศบาล รวมถึงเอกสารงบประมาณเพิ่มเติมที่เกี่ยวข้อง',
            'keywords' => ['เทศบัญญัติงบประมาณ', 'เทศบัญญัติ', 'งบประมาณรายจ่ายประจำปี', 'เพิ่มเติมฉบับ'],
            'sort_order' => 40,
        ],
        [
            'category_code' => 'article-9-4',
            'code' => 'article-9-4-manual',
            'label' => 'คู่มือการให้บริการข้อมูลข่าวสารของราชการ',
            'description' => 'คู่มือการให้บริการและการเข้าถึงข้อมูลข่าวสารของราชการ',
            'keywords' => ['คู่มือการให้บริการข้อมูลข่าวสาร', 'ข้อมูลข่าวสารของราชการ', 'คู่มือข้อมูลข่าวสาร'],
            'sort_order' => 10,
        ],
        [
            'category_code' => 'article-9-4',
            'code' => 'article-9-4-order',
            'label' => 'คำสั่งกำหนดแนวทางการปฏิบัติงานศูนย์บริการร่วม/บริการประชาชนของหน่วยงาน',
            'description' => 'คำสั่งหรือแนวทางการปฏิบัติงานของศูนย์บริการร่วมและงานบริการประชาชน',
            'keywords' => ['ศูนย์บริการร่วม', 'บริการประชาชน', 'คำสั่งกำหนดแนวทางการปฏิบัติงาน'],
            'sort_order' => 30,
        ],
        [
            'category_code' => 'article-9-4',
            'code' => 'article-9-4-form',
            'label' => 'คู่มือการขออนุญาตก่อสร้าง ดัดแปลง เคลื่อนย้าย หรือรื้อถอนอาคาร',
            'description' => 'คู่มือหรือขั้นตอนการขออนุญาตด้านอาคารที่มีผลต่อสิทธิของประชาชน',
            'keywords' => ['คู่มือการขออนุญาตก่อสร้าง', 'ดัดแปลง', 'เคลื่อนย้าย', 'รื้อถอนอาคาร'],
            'sort_order' => 20,
        ],
        [
            'category_code' => 'article-9-5',
            'code' => 'article-9-5-reference-print',
            'label' => 'ประกาศสภาวิชาชีพ เรื่องมาตรฐานการบัญชี ฉบับที่ 40 (ปรับปรุง 2558) เรื่องอสังหาริมทรัพย์เพื่อการลงทุน',
            'description' => 'ตัวอย่างสิ่งพิมพ์หรือเอกสารอ้างอิงที่มีการอ้างถึงตามมาตรา 7 วรรคสอง',
            'keywords' => ['มาตรฐานการบัญชี', 'ฉบับที่ 40', 'ปรับปรุง 2558', 'อสังหาริมทรัพย์เพื่อการลงทุน'],
            'sort_order' => 10,
        ],
        [
            'category_code' => 'article-9-6',
            'code' => 'article-9-6-public-service',
            'label' => 'สัญญากำจัดขยะขององค์กรปกครองส่วนท้องถิ่นจังหวัดที่ร่วมทุนกับเอกชน',
            'description' => 'สัญญาสัมปทานหรือความร่วมมือด้านกำจัดขยะระหว่างหน่วยงานท้องถิ่นกับเอกชน',
            'keywords' => ['กำจัดขยะ', 'ร่วมทุนกับเอกชน', 'องค์กรปกครองส่วนท้องถิ่นจังหวัด'],
            'sort_order' => 10,
        ],
        [
            'category_code' => 'article-9-6',
            'code' => 'article-9-6-exclusive-right',
            'label' => 'สัมปทานการทำโรงโม่หิน',
            'description' => 'สัญญาสัมปทานหรือสิทธิผูกขาดในการดำเนินกิจการโรงโม่หิน',
            'keywords' => ['สัมปทานการทำโรงโม่หิน', 'โรงโม่หิน'],
            'sort_order' => 20,
        ],
        [
            'category_code' => 'article-9-6',
            'code' => 'article-9-6-joint-investment',
            'label' => 'สัมปทานให้เอกชนเก็บขยะมูลฝอยของท้องถิ่น',
            'description' => 'สัญญาที่ให้เอกชนดำเนินการเก็บขยะมูลฝอยหรือบริการที่มีลักษณะผูกขาด',
            'keywords' => ['เก็บขยะมูลฝอย', 'สัมปทานให้เอกชน', 'เก็บขยะ'],
            'sort_order' => 30,
        ],
        [
            'category_code' => 'article-9-7',
            'code' => 'article-9-7-cabinet-resolution',
            'label' => 'มติ ครม. ที่เกี่ยวข้องกับหน่วยงานส่วนท้องถิ่น',
            'description' => 'มติ ครม. ที่เกี่ยวข้องกับงานท้องถิ่นหรือการดำเนินงานของหน่วยงาน',
            'keywords' => ['มติ ครม.', 'มติครม', 'คณะรัฐมนตรี'],
            'sort_order' => 10,
        ],
        [
            'category_code' => 'article-9-7',
            'code' => 'article-9-7-council-resolution',
            'label' => 'มติคณะกรรมการที่แต่งตั้งโดยกฎหมาย เช่น มติของสภาท้องถิ่น',
            'description' => 'มติจากคณะกรรมการที่แต่งตั้งโดยกฎหมาย รวมถึงมติของสภาท้องถิ่นและรายงานการประชุม',
            'keywords' => ['มติของสภาท้องถิ่น', 'สภาท้องถิ่น', 'ประชุมสภา', 'รายงานการประชุมสภา'],
            'sort_order' => 20,
        ],
        [
            'category_code' => 'article-9-8',
            'code' => 'article-9-8-procurement-plan',
            'label' => 'ข้อมูลข่าวสารผลการพิจารณาจัดซื้อจัดจ้างของหน่วยงานเป็นรายเดือนทุกๆ เดือน ตามแบบ สขร.1',
            'description' => 'รายงานผลการจัดซื้อจัดจ้างรายเดือนตามแบบ สขร.1 ตามตัวอย่างในเอกสารอ้างอิง',
            'keywords' => ['สขร', 'สขร.1', 'จัดซื้อจัดจ้าง', 'ผลการพิจารณาจัดซื้อจัดจ้าง', 'รายงานผลการจัดซื้อจัดจ้าง'],
            'sort_order' => 10,
        ],
        [
            'category_code' => 'article-9-8',
            'code' => 'article-9-8-other-disclosure',
            'label' => 'ข้อมูลข่าวสารเกณฑ์มาตรฐานความโปร่งใสฯ',
            'description' => 'ข้อมูลข่าวสารอื่นตามมาตรา 9(8) เช่น ข้อมูลความโปร่งใส การประเมินความเสี่ยงทุจริต รายงานการเงิน และความรู้ตามกฎหมายข้อมูลข่าวสาร',
            'keywords' => ['ความโปร่งใส', 'ทุจริต', 'สินบน', 'ความเสี่ยง', 'รายงานการเงิน', 'ตรวจเงินแผ่นดิน', 'พระราชบัญญัติข้อมูลข่าวสาร', 'เผยแพร่ความรู้'],
            'sort_order' => 20,
        ],
    ];
}

function ensure_document_library_schema(mysqli $conn): void
{
    static $schemaEnsured = false;
    if ($schemaEnsured) {
        return;
    }

    $schemaEnsured = true;

    ensure_document_category_table($conn);
    ensure_document_subcategory_table($conn);

    ensure_table_column($conn, 'items', 'category_id', 'INT(11) DEFAULT NULL AFTER details');
    ensure_table_column($conn, 'items', 'subcategory_id', 'INT(11) DEFAULT NULL AFTER category_id');
    ensure_table_column($conn, 'items', 'fiscal_year', 'SMALLINT UNSIGNED DEFAULT NULL AFTER subcategory_id');
    ensure_table_column($conn, 'items', 'document_date', 'DATE DEFAULT NULL AFTER fiscal_year');
    ensure_table_index($conn, 'items', 'idx_items_category_id', 'category_id');
    ensure_table_index($conn, 'items', 'idx_items_subcategory_id', 'subcategory_id');
    ensure_table_index($conn, 'items', 'idx_items_fiscal_year', 'fiscal_year');

    ensure_table_column($conn, 'items_backup', 'category_id', 'INT(11) DEFAULT NULL AFTER details');
    ensure_table_column($conn, 'items_backup', 'subcategory_id', 'INT(11) DEFAULT NULL AFTER category_id');
    ensure_table_column($conn, 'items_backup', 'fiscal_year', 'SMALLINT UNSIGNED DEFAULT NULL AFTER subcategory_id');
    ensure_table_column($conn, 'items_backup', 'document_date', 'DATE DEFAULT NULL AFTER fiscal_year');
    ensure_table_index($conn, 'items_backup', 'idx_items_backup_category_id', 'category_id');
    ensure_table_index($conn, 'items_backup', 'idx_items_backup_subcategory_id', 'subcategory_id');

    seed_document_categories($conn);
    seed_document_subcategories($conn);
    backfill_document_metadata($conn);
}

function ensure_document_category_table(mysqli $conn): void
{
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS document_categories (
    id INT(11) NOT NULL AUTO_INCREMENT,
    code VARCHAR(80) NOT NULL,
    reference_label VARCHAR(60) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    sort_order INT(11) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_document_categories_code (code),
    KEY idx_document_categories_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL;

    if (!$conn->query($sql)) {
        throw new RuntimeException('Unable to create document_categories table: ' . $conn->error);
    }
}

function ensure_document_subcategory_table(mysqli $conn): void
{
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS document_subcategories (
    id INT(11) NOT NULL AUTO_INCREMENT,
    category_id INT(11) NOT NULL,
    code VARCHAR(120) NOT NULL,
    label VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    sort_order INT(11) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_document_subcategories_code (code),
    KEY idx_document_subcategories_category_sort (category_id, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL;

    if (!$conn->query($sql)) {
        throw new RuntimeException('Unable to create document_subcategories table: ' . $conn->error);
    }
}

function ensure_table_column(mysqli $conn, string $table, string $column, string $definition): void
{
    $sql = "SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Unable to inspect schema: ' . $conn->error);
    }

    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = (int) ($result->fetch_assoc()['total'] ?? 0) > 0;
    $stmt->close();

    if ($exists) {
        return;
    }

    $alterSql = sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $table, $column, $definition);
    if (!$conn->query($alterSql)) {
        throw new RuntimeException(sprintf('Unable to alter %s.%s: %s', $table, $column, $conn->error));
    }
}

function ensure_table_index(mysqli $conn, string $table, string $indexName, string $columnList): void
{
    $sql = "SELECT COUNT(*) AS total FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Unable to inspect indexes: ' . $conn->error);
    }

    $stmt->bind_param('ss', $table, $indexName);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = (int) ($result->fetch_assoc()['total'] ?? 0) > 0;
    $stmt->close();

    if ($exists) {
        return;
    }

    $alterSql = sprintf('ALTER TABLE `%s` ADD INDEX `%s` (%s)', $table, $indexName, $columnList);
    if (!$conn->query($alterSql)) {
        throw new RuntimeException(sprintf('Unable to add index %s on %s: %s', $indexName, $table, $conn->error));
    }
}

function seed_document_categories(mysqli $conn): void
{
    $existing = [];
    $result = $conn->query('SELECT id, code FROM document_categories');
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $existing[$row['code']] = (int) $row['id'];
        }
    }

    $insertStmt = $conn->prepare(
        'INSERT INTO document_categories (code, reference_label, title, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, 1)'
    );
    $updateStmt = $conn->prepare(
        'UPDATE document_categories SET reference_label = ?, title = ?, description = ?, sort_order = ?, is_active = 1 WHERE code = ?'
    );

    if (!$insertStmt || !$updateStmt) {
        throw new RuntimeException('Unable to prepare category seed statements: ' . $conn->error);
    }

    foreach (document_category_seed_rows() as $row) {
        if (isset($existing[$row['code']])) {
            $updateStmt->bind_param(
                'sssis',
                $row['reference_label'],
                $row['title'],
                $row['description'],
                $row['sort_order'],
                $row['code']
            );
            $updateStmt->execute();
            continue;
        }

        $insertStmt->bind_param(
            'ssssi',
            $row['code'],
            $row['reference_label'],
            $row['title'],
            $row['description'],
            $row['sort_order']
        );
        $insertStmt->execute();
    }

    $insertStmt->close();
    $updateStmt->close();
}

function seed_document_subcategories(mysqli $conn): void
{
    $categoryCodeMap = map_document_category_ids_by_code($conn);
    if ($categoryCodeMap === []) {
        return;
    }

    $existing = [];
    $result = $conn->query('SELECT id, code FROM document_subcategories');
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $existing[$row['code']] = (int) $row['id'];
        }
    }

    $insertStmt = $conn->prepare(
        'INSERT INTO document_subcategories (category_id, code, label, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?, 1)'
    );
    $updateStmt = $conn->prepare(
        'UPDATE document_subcategories SET category_id = ?, label = ?, description = ?, sort_order = ?, is_active = 1 WHERE code = ?'
    );

    if (!$insertStmt || !$updateStmt) {
        throw new RuntimeException('Unable to prepare subcategory seed statements: ' . $conn->error);
    }

    $seedRows = document_subcategory_seed_rows();
    $activeCodes = [];

    foreach ($seedRows as $row) {
        $categoryId = $categoryCodeMap[$row['category_code']] ?? null;
        if ($categoryId === null) {
            continue;
        }

        $activeCodes[] = $row['code'];

        if (isset($existing[$row['code']])) {
            $updateStmt->bind_param(
                'issis',
                $categoryId,
                $row['label'],
                $row['description'],
                $row['sort_order'],
                $row['code']
            );
            $updateStmt->execute();
            continue;
        }

        $insertStmt->bind_param(
            'isssi',
            $categoryId,
            $row['code'],
            $row['label'],
            $row['description'],
            $row['sort_order']
        );
        $insertStmt->execute();
    }

    $insertStmt->close();
    $updateStmt->close();

    if ($activeCodes === []) {
        return;
    }

    $placeholders = implode(', ', array_fill(0, count($activeCodes), '?'));
    $deactivateSql = "UPDATE document_subcategories SET is_active = 0 WHERE code NOT IN ({$placeholders})";
    $deactivateStmt = $conn->prepare($deactivateSql);
    if ($deactivateStmt) {
        bind_dynamic_params($deactivateStmt, str_repeat('s', count($activeCodes)), $activeCodes);
        $deactivateStmt->execute();
        $deactivateStmt->close();
    }
}

function backfill_document_metadata(mysqli $conn): void
{
    $categoryCodeMap = map_document_category_ids_by_code($conn);
    if ($categoryCodeMap === []) {
        return;
    }

    $subcategoryRules = document_subcategory_rule_map($conn, $categoryCodeMap);
    $subcategoryToCategory = [];
    foreach ($subcategoryRules as $rule) {
        $subcategoryToCategory[$rule['id']] = $rule['category_id'];
    }

    $result = $conn->query('SELECT id, name, details, category_id, subcategory_id, fiscal_year FROM items');
    if (!$result instanceof mysqli_result) {
        return;
    }

    $updateStmt = $conn->prepare(
        'UPDATE items SET category_id = COALESCE(?, category_id), subcategory_id = COALESCE(?, subcategory_id), fiscal_year = COALESCE(?, fiscal_year) WHERE id = ?'
    );
    if (!$updateStmt) {
        throw new RuntimeException('Unable to prepare item metadata backfill: ' . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $text = trim((string) ($row['name'] ?? '') . ' ' . (string) ($row['details'] ?? ''));
        $currentCategoryId = $row['category_id'] !== null ? (int) $row['category_id'] : null;
        $currentSubcategoryId = $row['subcategory_id'] !== null ? (int) $row['subcategory_id'] : null;
        $currentFiscalYear = $row['fiscal_year'] !== null ? (int) $row['fiscal_year'] : null;

        $inferredCategoryId = infer_document_category_id($text, $categoryCodeMap);
        $categoryId = $inferredCategoryId ?? $currentCategoryId;
        $subcategoryId = infer_document_subcategory_id($text, $categoryId, $subcategoryRules) ?? $currentSubcategoryId;
        if ($categoryId === null && $subcategoryId !== null && isset($subcategoryToCategory[$subcategoryId])) {
            $categoryId = $subcategoryToCategory[$subcategoryId];
        }
        $fiscalYear = $currentFiscalYear ?? infer_document_fiscal_year($text);

        if ($categoryId === null && $subcategoryId === null && $fiscalYear === null) {
            continue;
        }

        if ($categoryId === $currentCategoryId && $subcategoryId === $currentSubcategoryId && $fiscalYear === $currentFiscalYear) {
            continue;
        }

        $itemId = (int) $row['id'];
        $updateStmt->bind_param('iiii', $categoryId, $subcategoryId, $fiscalYear, $itemId);
        $updateStmt->execute();
    }

    $updateStmt->close();
}

function map_document_category_ids_by_code(mysqli $conn): array
{
    $map = [];
    $result = $conn->query('SELECT id, code FROM document_categories');
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $map[$row['code']] = (int) $row['id'];
        }
    }
    return $map;
}

function document_subcategory_rule_map(mysqli $conn, array $categoryCodeMap): array
{
    $subcategoryIdByCode = [];
    $result = $conn->query('SELECT id, code FROM document_subcategories');
    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $subcategoryIdByCode[$row['code']] = (int) $row['id'];
        }
    }

    $rules = [];
    foreach (document_subcategory_seed_rows() as $row) {
        if (!isset($subcategoryIdByCode[$row['code']])) {
            continue;
        }
        $categoryId = $categoryCodeMap[$row['category_code']] ?? null;
        if ($categoryId === null) {
            continue;
        }
        $rules[] = [
            'id' => $subcategoryIdByCode[$row['code']],
            'category_id' => $categoryId,
            'keywords' => $row['keywords'],
        ];
    }

    return $rules;
}

function infer_document_category_id(string $text, array $codeMap): ?int
{
    $normalized = normalize_document_text($text);
    if ($normalized === '') {
        return null;
    }

    $rules = [
        'article-9-6' => ['สัมปทาน', 'ผูกขาด', 'ร่วมทุน', 'กำจัดขยะ', 'โรงโม่หิน', 'เก็บขยะมูลฝอย'],
        'article-9-7' => ['มติ', 'ประชุมสภา', 'รายงานการประชุม'],
        'article-9-4' => ['คู่มือการให้บริการข้อมูลข่าวสาร', 'คู่มือการขออนุญาตก่อสร้าง', 'ศูนย์บริการร่วม', 'คำสั่ง'],
        'article-9-8' => ['จัดซื้อจัดจ้าง', 'สขร', 'ความโปร่งใส', 'ทุจริต', 'สินบน', 'ความเสี่ยง', 'รายงานการเงิน', 'ตรวจเงินแผ่นดิน', 'พระราชบัญญัติข้อมูลข่าวสาร', 'เผยแพร่ความรู้'],
        'article-9-3' => ['แผนการดำเนินงาน', 'แผนการการดำเนินงาน', 'แผนพัฒนาท้องถิ่น', 'ยุทธศาสตร์การพัฒนา', 'ติดตามและประเมินผล', 'งบประมาณ', 'เทศบัญญัติ', 'ข้อบัญญัติ', 'โครงการ'],
        'article-9-1' => ['อนุญาต', 'ก่อสร้าง', 'ดัดแปลง', 'รื้อถอน', 'อาคาร', 'ตลาด', 'นอกเขตเทศบาล'],
        'article-9-2' => ['คำแถลงนโยบาย', 'นโยบาย', 'การตีความ'],
        'article-9-5' => ['สิ่งพิมพ์', 'เอกสารอ้างอิง', 'มาตรฐาน', 'มาตรฐานการบัญชี', 'อสังหาริมทรัพย์เพื่อการลงทุน'],
        'article-7' => ['ราชกิจจานุเบกษา', 'โครงสร้างการจัดองค์กร', 'อำนาจหน้าที่', 'สถานที่ติดต่อ', 'กิจการที่เป็นอันตรายต่อสุขภาพ'],
    ];

    foreach ($rules as $code => $keywords) {
        if (!isset($codeMap[$code])) {
            continue;
        }
        foreach ($keywords as $keyword) {
            $needle = normalize_document_text($keyword);
            $position = function_exists('mb_strpos')
                ? mb_strpos($normalized, $needle, 0, 'UTF-8')
                : strpos($normalized, strtolower($keyword));
            if ($position !== false) {
                return $codeMap[$code];
            }
        }
    }

    return null;
}

function infer_document_subcategory_id(string $text, ?int $categoryId, array $rules): ?int
{
    $normalized = normalize_document_text($text);
    if ($normalized === '') {
        return null;
    }

    foreach ($rules as $rule) {
        if ($categoryId !== null && (int) $rule['category_id'] !== (int) $categoryId) {
            continue;
        }
        foreach ($rule['keywords'] as $keyword) {
            $needle = normalize_document_text($keyword);
            $position = function_exists('mb_strpos')
                ? mb_strpos($normalized, $needle, 0, 'UTF-8')
                : strpos($normalized, strtolower($keyword));
            if ($position !== false) {
                return (int) $rule['id'];
            }
        }
    }

    return null;
}

function infer_document_fiscal_year(string $text): ?int
{
    if (!preg_match('/25\d{2}/', $text, $match)) {
        return null;
    }

    return (int) $match[0];
}

function normalize_document_text(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    if (function_exists('mb_strtolower')) {
        return mb_strtolower($text, 'UTF-8');
    }

    return strtolower($text);
}

function get_document_categories(mysqli $conn, bool $activeOnly = true): array
{
    $sql = 'SELECT id, code, reference_label, title, description, sort_order, is_active
            FROM document_categories';
    if ($activeOnly) {
        $sql .= ' WHERE is_active = 1';
    }
    $sql .= ' ORDER BY sort_order ASC, id ASC';

    $result = $conn->query($sql);
    if (!$result instanceof mysqli_result) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

function get_document_category_map(mysqli $conn): array
{
    $map = [];
    foreach (get_document_categories($conn, false) as $row) {
        $map[(int) $row['id']] = $row;
    }
    return $map;
}

function get_document_subcategories(mysqli $conn, ?int $categoryId = null, bool $activeOnly = true): array
{
    $sql = 'SELECT ds.id, ds.category_id, ds.code, ds.label, ds.description, ds.sort_order, ds.is_active,
                   dc.code AS category_code, dc.reference_label AS category_reference, dc.title AS category_title
            FROM document_subcategories ds
            LEFT JOIN document_categories dc ON dc.id = ds.category_id';

    $conditions = [];
    $types = '';
    $params = [];
    if ($activeOnly) {
        $conditions[] = 'ds.is_active = 1';
    }
    if ($categoryId !== null) {
        $conditions[] = 'ds.category_id = ?';
        $types .= 'i';
        $params[] = $categoryId;
    }
    if ($conditions !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY COALESCE(dc.sort_order, 999), ds.sort_order ASC, ds.id ASC';

    if ($params === []) {
        $result = $conn->query($sql);
    } else {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [];
        }
        bind_dynamic_params($stmt, $types, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }

    if (!$result instanceof mysqli_result) {
        return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

function get_document_subcategory_map(mysqli $conn): array
{
    $map = [];
    foreach (get_document_subcategories($conn, null, false) as $row) {
        $map[(int) $row['id']] = $row;
    }
    return $map;
}

function get_document_subcategories_grouped(mysqli $conn, bool $activeOnly = true): array
{
    $grouped = [];
    foreach (get_document_subcategories($conn, null, $activeOnly) as $row) {
        $grouped[(int) $row['category_id']][] = $row;
    }
    return $grouped;
}

function get_document_fiscal_years(mysqli $conn): array
{
    $years = [];
    $result = $conn->query('SELECT DISTINCT fiscal_year FROM items WHERE fiscal_year IS NOT NULL ORDER BY fiscal_year DESC');
    if (!$result instanceof mysqli_result) {
        return $years;
    }

    while ($row = $result->fetch_assoc()) {
        $years[] = (int) $row['fiscal_year'];
    }

    return $years;
}

function document_category_label(array $category): string
{
    return trim(($category['reference_label'] ?? '') . ' - ' . ($category['title'] ?? ''));
}

function document_subcategory_label(array $subcategory): string
{
    return trim((string) ($subcategory['label'] ?? ''));
}

function bind_dynamic_params(mysqli_stmt $stmt, string $types, array $params): void
{
    if ($types === '' || $params === []) {
        return;
    }

    $stmt->bind_param($types, ...$params);
}
