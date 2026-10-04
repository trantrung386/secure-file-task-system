# Hướng dẫn Triển khai Hybrid Encryption (RSA + AES)

Tài liệu này hướng dẫn cách triển khai hệ thống mã hóa lai (Hybrid Encryption) sử dụng RSA để bảo vệ khóa AES.

## 📋 Tổng quan

- **AES-256-CBC**: Mã hóa nội dung file
- **RSA-2048 (OAEP)**: Bọc (wrap) khóa AES để bảo vệ
- **Lưu trữ**: Khóa AES được bọc (encrypted_key) + IV lưu trong database

## 🔧 Các bước triển khai

### 1️⃣ Backup Database Hiện Tại
```bash
mysqldump -u root -p data > backup_data_$(date +%s).sql
```

### 2️⃣ Chạy Migration SQL
```bash
mysql -u root -p data < database/migration_add_rsa_columns.sql
```

**Hoặc chạy trực tiếp trong MySQL:**
```sql
ALTER TABLE `tep_dinh_kem` 
ADD COLUMN `encrypted_key` longtext DEFAULT NULL COMMENT 'Khóa AES được bọc bằng RSA (base64)' AFTER `upload_reason`,
ADD COLUMN `iv` varchar(255) DEFAULT NULL COMMENT 'IV cho AES-256-CBC (base64)' AFTER `encrypted_key`;
```

### 3️⃣ Kiểm tra cấu trúc bảng
```sql
DESC tep_dinh_kem;
```

Kết quả mong muốn:
```
| Field          | Type         | Null | Key | Default |
|----------------|--------------|------|-----|---------|
| encrypted_key  | longtext     | YES  |     | NULL    |
| iv             | varchar(255) | YES  |     | NULL    |
```

### 4️⃣ Reload Ứng dụng
Vào trang chính của ứng dụng hoặc chạy:
```bash
php index.php
```

Lần đầu tiên, ứng dụng sẽ:
- Tạo cặp khóa RSA 2048-bit
- Lưu private key: `storage/rsa_private.pem` (permission 0600)
- Lưu public key: `storage/rsa_public.pem`

### 5️⃣ Kiểm tra RSA Keys
```bash
ls -la storage/rsa_*.pem
```

Kết quả:
```
-rw------- rsa_private.pem
-rw-r--r-- rsa_public.pem
```

## 📊 Cơ chế Hoạt động

### Upload File
1. Tạo khóa AES ngẫu nhiên (32 bytes)
2. Mã hóa nội dung file bằng AES-256-CBC
3. Mã hóa khóa AES bằng RSA public key → `encrypted_key`
4. Lưu vào database:
   - `encrypted_path`: Đường dẫn file mã hóa (chứa IV + ciphertext)
   - `encrypted_key`: Khóa AES bọc RSA (base64)
   - `iv`: IV của AES (base64)

### Download File
1. Lấy `encrypted_key` từ database
2. Giải mã khóa AES bằng RSA private key
3. Lấy file từ `encrypted_path` (chứa IV + ciphertext)
4. Giải mã file bằng AES-256-CBC
5. Trả cho người dùng

## 🔐 Bảo mật

- **Private Key**: Chỉ lưu trên server, không chia sẻ
- **Public Key**: Dùng để mã hóa lúc upload
- **File**: Lưu mã hóa trong `storage/encrypted/`
- **Permission**: Private key có permission 0600 (chỉ owner đọc)

## ✅ Kiểm tra Hệ thống

### Test Upload
1. Login vào hệ thống
2. Tạo công việc hoặc dự án
3. Upload file
4. Kiểm tra database:
```sql
SELECT id, original_name, encrypted_key, iv FROM tep_dinh_kem LIMIT 1;
```

Kết quả: `encrypted_key` và `iv` không NULL

### Test Download
1. Nhấn tải file
2. File được trả về đúng nội dung
3. Kiểm tra logs: `app/logs/` (nếu có)

## 🐛 Troubleshooting

### Lỗi: "Không thể lưu RSA keypair vào storage"
- Kiểm tra quyền ghi `storage/` directory
```bash
chmod 755 storage
```

### Lỗi: "Không thể đọc RSA public key"
- Kiểm tra file tồn tại:
```bash
ls -la storage/rsa_*.pem
```
- Tạo lại keys bằng cách xóa files cũ hoặc gọi `ensureRsaKeysExist()` lại

### Upload thất bại nhưng không có lỗi
- Kiểm tra error log:
```bash
tail -f storage/error.log
```
- Kiểm tra quyền OpenSSL
- Chạy php `-i | grep OpenSSL` để xác nhận OpenSSL được enable

## 📝 Các files thay đổi

- `database/data.sql` - Schema bảng `tep_dinh_kem`
- `database/migration_add_rsa_columns.sql` - Migration script
- `app/services/FileCryptoService.php` - Thêm methods RSA management
- `app/models/Attachment.php` - Hỗ trợ `encrypted_key`, `iv`
- `app/controllers/TaskController.php` - Sử dụng `hybridEncryptAndStore`
- `app/controllers/ProjectController.php` - Sử dụng `hybridEncryptAndStore`
- `app/controllers/FileController.php` - Hỗ trợ `hybridDecrypt`
- `app/bootstrap.php` - Khởi tạo RSA keys

## 🔄 Rollback (Nếu cần)

```bash
# Backup data trước
mysqldump -u root -p data > backup_after_migration.sql

# Restore lại
mysql -u root -p data < backup_data_*.sql

# Xóa files RSA keys
rm storage/rsa_*.pem
```

---
**Lần cập nhật**: 2026-06-02
