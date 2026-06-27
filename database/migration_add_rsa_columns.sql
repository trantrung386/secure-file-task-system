-- Migration: Add RSA encrypted key and IV columns to tep_dinh_kem
-- Date: 2026-06-02
-- Description: Add encrypted_key and iv columns to support hybrid encryption (RSA + AES)

ALTER TABLE `tep_dinh_kem` 
ADD COLUMN `encrypted_key` longtext DEFAULT NULL COMMENT 'Khóa AES được bọc bằng RSA (base64)' AFTER `upload_reason`,
ADD COLUMN `iv` varchar(255) DEFAULT NULL COMMENT 'IV cho AES-256-CBC (base64)' AFTER `encrypted_key`;

-- Verify the changes
DESC `tep_dinh_kem`;
