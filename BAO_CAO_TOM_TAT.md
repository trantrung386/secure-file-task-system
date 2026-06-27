# Tóm tắt code để đưa vào báo cáo khóa luận

## 1. Tên đề tài hiện thực
**Project_App - Web app giao việc theo nhóm/dự án có mã hóa file và tính KPI**.

Hệ thống được xây dựng để quản lý công việc tập trung, hỗ trợ tạo việc, phân công, theo dõi tiến độ, duyệt đánh giá kết quả và thống kê KPI. Đồng thời, file đính kèm được mã hóa trước khi lưu và chỉ giải mã khi người dùng có quyền truy cập.

## 2. Kiến trúc MVC áp dụng
### Controller
Controller tiếp nhận request, kiểm tra quyền, gọi model xử lý dữ liệu rồi trả về view.
- `AuthController`: đăng nhập, đăng xuất
- `UserController`: quản lý tài khoản
- `ProjectController`: quản lý dự án, thành viên dự án
- `TaskController`: tạo việc, phân công, cập nhật trạng thái, duyệt đánh giá, upload file
- `FileController`: tải file và giải mã theo quyền
- `KpiController`: báo cáo KPI
- `LogController`: nhật ký truy cập

### Model
Model thao tác trực tiếp với MySQL qua PDO.
- `User`
- `Project`
- `Task`
- `Attachment`
- `Log`

### View
View hiển thị giao diện thực tế gồm:
- đăng nhập
- dashboard tổng quan
- quản lý tài khoản
- quản lý dự án
- thành viên dự án
- quản lý công việc
- phân công công việc
- duyệt và đánh giá
- KPI
- nhật ký truy cập

## 3. CSDL MySQL
Tên database: **Project_App**

Các bảng chính dùng tên tiếng Việt không dấu để dễ đọc và tránh lỗi encoding:
- `tai_khoan`
- `du_an`
- `thanh_vien_du_an`
- `cong_viec`
- `lich_su_trang_thai_cong_viec`
- `tep_dinh_kem`
- `nhat_ky_truy_cap`

## 4. Các chức năng đã hiện thực theo đề bài
### Admin
- quản lý tài khoản
- khóa/mở tài khoản
- phân vai trò
- xem log truy cập

### Trưởng nhóm / Quản lý
- tạo dự án
- cập nhật dự án
- thêm/xóa thành viên dự án
- tạo công việc
- phân công công việc
- duyệt và đánh giá

### Nhân viên
- xem công việc được giao
- cập nhật tiến độ
- upload file kết quả
- tải file hợp lệ
- xem KPI cá nhân

## 5. Điểm nhấn kỹ thuật
- kết nối MySQL bằng PDO
- password hash bằng `password_hash`
- file mã hóa bằng AES-256-CBC
- kiểm tra quyền truy cập file trước khi giải mã
- có log thao tác phục vụ truy vết
- có KPI tính từ trạng thái, deadline và điểm review

## 6. Lý do code dễ sửa lỗi
- route chia theo module rõ ràng: `tai_khoan`, `du_an`, `cong_viec`, `kpi`, `nhat_ky`
- controller/model đặt tên chuẩn, dễ search
- hàm đều có ghi chú tiếng Việt
- giao diện chia đúng theo use case để đối chiếu báo cáo
