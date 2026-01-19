
// แก้รายละเอียดในตาราง
const booksData = [
    { id: 1, img: 'images/เทศบัญญัติv12567.jpeg', รายละเอียด: 'เทศบัญญัติงบประมาณรายจ่ายประจำปีงบประมาณพ.ศ.2567  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;จำนวน 714 หน้า ', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2023-10_ff8761720eb2dc6.pdf' },
    { id: 2, img: 'images/เทศบัญญัติv22567.jpeg', รายละเอียด: 'เทศบัญญัติงบประมาณรายจ่ายเพิ่มเติมฉบับที่2ประจำปีงบประมาณพ.ศ.2567  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; จำนวน 73 หน้า ', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2024-09_c566ab2c181e5c1.pdf' },
    { id: 3, img: 'images/เทศบัญญัติv12568.jpeg', รายละเอียด: 'เทศบัญญัติงบประมาณรายจ่ายประจำปีงบประมาณพ.ศ.2568 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; จำนวน 668 หน้า ', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2568, link: 'https://phsmun.go.th/files/com_strategy/2024-09_e61a5095d3f2b9c.pdf' },
    { id: 4, img: 'images/ita67.jpeg', รายละเอียด: 'รายงานมาตรการส่งเสริมคุณธรรมและความโปร่งใสประจำปีงบประมาณพ.ศ.2567 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;   จำนวน 21 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2024-04_90488576f414a60.pdf' },
    { id: 5, img: 'images/แผนพัฒ66-70.jpeg', รายละเอียด: 'รายงานการติดตามและประเมินผล แผนพัฒนาท้องถิ่น(2566-2570)ประจำปีงบประมาณพ.ศ.2566 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 373 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2024-10_7400730b81248a6.pdf' },
    { id: 6, img: 'images/แจ้งปรับปรุงข้อมูลความเชื่อมโยงยุทธศาสตร์2567.png', รายละเอียด: 'แจ้งปรับปรุงข้อมูลความเชื่อมโยงยุทธศาสตร์การพัฒนาของเทศบาลนครพิษณุโลก &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 29 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2023-04_0818af4abd6f952.pdf' },
    { id: 7, img: 'images/คู่มือ01.png', รายละเอียด: 'คู่มือการปฏิบัติงานขององค์กรปกครองส่วนท้องถิ่น &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 154 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2566, link: 'https://phsmun.go.th/files/com_news_center/2024-04_7bed49ee909bdb6.pdf' },
    { id: 8, img: 'images/แผนการดำเนินการ0167.png', รายละเอียด: 'แผนการดำเนินงานประจำปีงบประมาณพ.ศ.2567 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 274 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2023-11_18c9474eb868d01.pdf' },
    { id: 9, img: 'images/แผนการดำเนินการ0267.png', รายละเอียด: 'แผนการดำเนินงานประจำปีงบประมาณพ.ศ.2567เพิ่มเติมครั้งที่1 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 16 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2024-01_96ca1d88b9252ad.pdf' },
    { id: 10, img: 'images/แผนการดำเนินการ0367.png', รายละเอียด: 'แผนการการดำเนินงานประจำปีงบประมาณพ.ศ.ครั้งที่2 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 36 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2024-05_f5797b4b7e3b7b8.pdf' },
    { id: 11, img: 'images/แผนการดำเนินการ0467.png', รายละเอียด: 'แผนการดำเนินงานประจำปีงบประมาณพ.ศ.2568 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 224 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2568, link: 'https://phsmun.go.th/files/com_strategy/2024-10_7f9e3915602e89e.pdf' },
    { id: 12, img: 'images/แผนบริหารความเสี่ยง0167.png', รายละเอียด: 'แผนการบริหารจัดการความเสี่ยงประจำปีงบประมาณพ.ศ.2567 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 427 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2024-06_7543657b0093495.pdf' },
    { id: 13, img: 'images/แผนประเมินความเสี่ยง0167.png', รายละเอียด: 'การประเมินความเสี่ยงการทุจริตในประเด็นที่เกี่ยวข้องกับสินบนประจําปีงบประมาณ2567 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 18 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2567, link: 'https://phsmun.go.th/files/com_strategy/2024-02_7a693c1ae28943a.pdf' },
    { id: 14, img: 'images/แผนพัฒ0167.png', รายละเอียด: 'แผนพัฒนาท้องถิ่น(พ.ศ.2566-2570)ฉบับทบทวนพ.ศ.2566เทศบาลนครพิษณุโลก &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; จำนวน 862 หน้า', ผู้จัดทำ: 'เทศบาลนครพิษณุโลก', ปี: 2566, link: 'https://phsmun.go.th/files/com_strategy/2023-09_8fc21c005e3ca1c.pdf' },
];

function displayBooks(page) {
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = page * itemsPerPage;
    const paginatedBooks = filteredBooks.slice(startIndex, endIndex);

    const bookTableBody = document.getElementById('bookTableBody');
    bookTableBody.innerHTML = '';

    paginatedBooks.forEach(book => {
        const row = `
                <tr>
                    <td>${book.id}</td>
                    <td><img src="${book.img}" alt="Book ${book.id}" class="img-fluid" style="max-width: 120%; height: auto;"></td>
                    <td><a href="${book.link}" target="_blank">${book.รายละเอียด}</a></td>
                    <td>${book.ผู้จัดทำ}</td>
                    <td>${book.ปี}</td>
                </tr>
            `;
        bookTableBody.innerHTML += row;
    });
}

//กำหนดจำนวนหนังสือในตาราง 5 แล้วขึ้นหน้าใหม่
const itemsPerPage = 5;
let currentPage = 1;
let filteredBooks = booksData;

// ฟังก์ชันแสดงหนังสือในตาราง
function displayBooks(page) {
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = page * itemsPerPage;
    const paginatedBooks = filteredBooks.slice(startIndex, endIndex);

    const bookTableBody = document.getElementById('bookTableBody');
    bookTableBody.innerHTML = '';

    paginatedBooks.forEach(book => {
        const row = document.createElement('tr');
        row.setAttribute('data-link', book.link);
        row.innerHTML = `
                    <td>${book.id}</td>
                    <td><img src="${book.img}" alt="${book.รายละเอียด}"></td>
                    <td>${book.รายละเอียด}</td>
                    <td>${book.ผู้จัดทำ}</td>
                    <td>${book.ปี}</td>
                `;
        // เพิ่มการคลิกที่แถวของหนังสือเพื่อนำทางไปยังลิงก์
        row.addEventListener('click', function () {
            window.open(book.link, '_blank');
        });
        bookTableBody.appendChild(row);
    });
}

// ฟังก์ชันจัดการ Pagination
function setupPagination() {
    const totalPages = Math.ceil(filteredBooks.length / itemsPerPage);
    const paginationControls = document.getElementById('paginationControls');
    paginationControls.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
        const pageItem = document.createElement('li');
        pageItem.classList.add('page-item');
        if (i === currentPage) pageItem.classList.add('active');

        const pageLink = document.createElement('a');
        pageLink.classList.add('page-link');
        pageLink.textContent = i;
        pageLink.href = "#";
        pageLink.addEventListener('click', function (e) {
            e.preventDefault();
            currentPage = i;
            displayBooks(currentPage);
            setupPagination();
        });

        pageItem.appendChild(pageLink);
        paginationControls.appendChild(pageItem);
    }
}

// ฟังก์ชันค้นหา
document.getElementById('searchBook').addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    filteredBooks = booksData.filter(book =>
        book.รายละเอียด.toLowerCase().includes(searchValue) || book.ผู้จัดทำ.toLowerCase().includes(searchValue));
    currentPage = 1;
    displayBooks(currentPage);
    setupPagination();
});

// ฟังก์ชันกรองตามปี
document.getElementById('filterYear').addEventListener('change', function () {
    const selectedYear = this.value;
    filteredBooks = selectedYear ? booksData.filter(book => book.ปี == selectedYear) : booksData;
    currentPage = 1;
    displayBooks(currentPage);
    setupPagination();
});

// ฟังก์ชันรีเซ็ตการค้นหา
document.getElementById('resetBtn').addEventListener('click', function () {
    document.getElementById('searchBook').value = '';
    document.getElementById('filterYear').value = '';
    filteredBooks = booksData;
    currentPage = 1;
    displayBooks(currentPage);
    setupPagination();
});

// แสดงหนังสือเมื่อโหลดหน้า
displayBooks(currentPage);
setupPagination();

// ฟังก์ชันสลับการแสดงผลวิดีโอ
function toggleVideoContent(videoId) {
    const videoContent = document.getElementById(videoId);
    if (videoContent.style.display === "none") {
        videoContent.style.display = "block";
    } else {
        videoContent.style.display = "none";
    }
}
//  <!-- ฟังก์ชัน JavaScript สำหรับสลับโหมดกลางวัน/กลางคืน -->

// const toggleBtn = document.getElementById('toggleMode');

// ตั้งค่าเริ่มต้นให้เป็น Day-mode
// document.body.classList.add('day-mode');
// toggleBtn.innerHTML = '🌞'; // ตั้งไอคอนเริ่มต้นเป็นไอคอนกลางวัน

// toggleBtn.addEventListener('click', () => {
//     document.body.classList.toggle('day-mode');
//     if (document.body.classList.contains('day-mode')) {
//         toggleBtn.innerHTML = '🌞';
//     } else {
//         toggleBtn.innerHTML = '🌙';
//     }
// });

// ฟังก์ชัน JavaScript สำหรับสลับโหมดกลางวัน/กลางคืน header
// const toggleModeButton = document.getElementById('toggleMode'); // ปุ่มเปลี่ยนโหมด

// toggleModeButton.addEventListener('click', function () {
//     document.body.classList.toggle('dark-mode');
// });

// ฟังก์ชันการค้นหา
const searchInput = document.getElementById('searchInput');
const booksTable = document.getElementById('booksTable').getElementsByTagName('tbody')[0];
const resetBtn = document.getElementById('resetBtn');

searchInput.addEventListener('keyup', function () {
    const filter = searchInput.value.toLowerCase();
    const rows = booksTable.getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        const titleCell = rows[i].getElementsByTagName('td')[2]; const
            titleText = titleCell.textContent || titleCell.innerText; if (titleText.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = '';
            } else {
            rows[i].style.display = 'none';
        }
    }
});

resetBtn.addEventListener('click', function () {
    searchInput.value = '';
    const rows = booksTable.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) { rows[i].style.display = ''; }
}); // ฟังก์ชันการกรองตามปี const
filterYearSelect = document.getElementById('filterYearSelect'); filterYearSelect.addEventListener('change',
    function () {
        const filterValue = filterYearSelect.value; const rows = booksTable.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            const yearCell = rows[i].getElementsByTagName('td')[4]; const
                yearText = yearCell.textContent || yearCell.innerText; if (filterValue === 'all' || yearText === filterValue) {
                    rows[i].style.display = '';
                } else { rows[i].style.display = 'none'; }
        }
    }); // แสดงหนังสือเมื่อโหลดหน้า
displayBooks(currentPage); createPagination();

// <!-- JavaScript สำหรับการเปลี่ยนธีม -->

// เก็บธีมปัจจุบันใน localStorage เพื่อให้จำได้
// const currentTheme = localStorage.getItem('theme') || 'light';
// document.documentElement.setAttribute('data-theme', currentTheme);

// ฟังก์ชันเปลี่ยนธีม
// function toggleTheme() {
//     let theme = document.documentElement.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
//     document.documentElement.setAttribute('data-theme', theme);
//     localStorage.setItem('theme', theme);
// }
// เริ่มนับเวลาการซ่อนปุ่มใหม่หลังจากที่มีการคลิก
// resetAutoHideTimer();
// window.addEventListener('scroll', () => {
//     const footer = document.querySelector('.footer');
//     const scrollPosition = window.scrollY + window.innerHeight;
//     const footerHeight = Math.min(200, Math.max(100, scrollPosition / 10));

//     footer.style.height = `${footerHeight}px`;
// });







