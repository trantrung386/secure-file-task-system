# Project_App - Web app giao việc bằng PHP MVC + MySQL

Project này được dựng để bám sát đề tài khóa luận: **xây dựng web app giao việc theo nhóm/dự án, có mã hóa file đính kèm và tính KPI**. Các chức năng chính trong đề đều đã có bản demo chạy được: quản lý tài khoản, quản lý dự án, tạo/phân công công việc, cập nhật tiến độ, duyệt đánh giá, upload/download file mã hóa và báo cáo KPI.

## 1. Công nghệ sử dụng
- PHP 8+
- MySQL / MariaDB
- PDO
- MVC thuần PHP
- Bootstrap 5 + CSS tùy biến

## 2. Cấu trúc thư mục chính
```text
Project_App_MVC_Hoan_Chinh/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── core/
│   ├── models/
│   ├── services/
│   └── views/
├── assets/
├── database/
│   ├── Project_App.sql
│   └── Project_App_mysql_cu_utf8.sql
├── storage/
│   └── encrypted/
├── index.php
└── README.md
```

## 3. Cách chạy trên XAMPP
### Bước 1: copy project
Chép thư mục `Project_App_MVC_Hoan_Chinh` vào:
```text
C:\xampp\htdocs\
```

### Bước 2: tạo database và import
Trong phpMyAdmin, import file:
```text
database/Project_App_mysql_cu_utf8.sql
```
File này đã để charset `utf8` để hợp với XAMPP/MySQL cũ.

### Bước 3: kiểm tra file kết nối
Mở file `app/config/config.php`:
```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'Project_App');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Bước 4: chạy project
Mở trình duyệt:
```text
http://localhost/Project_App_MVC_Hoan_Chinh/
```
Hoặc nếu XAMPP của bạn dùng port 8080:
```text
http://localhost:8080/Project_App_MVC_Hoan_Chinh/
```

## 4. Tài khoản mẫu
- Admin: `admin@taskflow.local` / `admin123`
- Trưởng nhóm: `manager@taskflow.local` / `manager123`
- Nhân viên 1: `employee1@taskflow.local` / `employee123`
- Nhân viên 2: `employee2@taskflow.local` / `employee123`

## 5. Bám theo yêu cầu đề tài
### Admin
- Quản lý tài khoản
- Quản lý vai trò/trạng thái
- Xem nhật ký truy cập cơ bản

### Trưởng nhóm / Quản lý
- Tạo dự án
- Cập nhật dự án
- Thêm/xóa thành viên dự án
- Tạo công việc
- Phân công công việc
- Duyệt và đánh giá

### Nhân viên
- Xem danh sách công việc được giao
- Cập nhật trạng thái
- Upload file kết quả
- Download file hợp lệ
- Xem KPI cá nhân

## 6. Bảo mật file
- File được mã hóa AES-256-CBC trước khi lưu.
- Khi tải xuống, hệ thống kiểm tra quyền truy cập rồi mới giải mã.
- Mật khẩu lưu bằng `password_hash`.

## 7. Quy ước tên trong MySQL
Để đúng ý bạn và hạn chế lỗi encoding trên XAMPP cũ, mình dùng **tên bảng tiếng Việt không dấu**:
- `tai_khoan`
- `du_an`
- `thanh_vien_du_an`
- `cong_viec`
- `lich_su_trang_thai_cong_viec`
- `tep_dinh_kem`
- `nhat_ky_truy_cap`

## 8. Ghi chú cho việc sửa lỗi
- Tên route đặt theo module dễ tìm: `tai_khoan`, `du_an`, `cong_viec`, `kpi`, `nhat_ky`
- Hàm trong controller/model đã có ghi chú tiếng Việt
- Giao diện tách rõ menu và form để dễ demo báo cáo
