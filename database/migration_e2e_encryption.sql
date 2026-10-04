-- Migration: End-to-end encryption metadata
-- Server stores user public keys and one wrapped file key per recipient.

ALTER TABLE `tai_khoan`
ADD COLUMN `public_key_jwk` LONGTEXT DEFAULT NULL AFTER `password`;

ALTER TABLE `tep_dinh_kem`
MODIFY `task_id` int(11) DEFAULT NULL;

ALTER TABLE `tep_dinh_kem`
MODIFY `encrypted_path` varchar(255) DEFAULT NULL;

ALTER TABLE `tep_dinh_kem`
ADD COLUMN `project_id` int(11) DEFAULT NULL AFTER `task_id`;

ALTER TABLE `tep_dinh_kem`
ADD KEY `idx_tep_dinh_kem_project` (`project_id`);

ALTER TABLE `tep_dinh_kem`
ADD COLUMN `module_name` varchar(50) NOT NULL DEFAULT 'cong_viec' AFTER `project_id`;

ALTER TABLE `tep_dinh_kem`
ADD COLUMN `encrypted_blob` LONGBLOB DEFAULT NULL AFTER `encrypted_path`;

ALTER TABLE `tep_dinh_kem`
ADD COLUMN `storage_mode` varchar(20) NOT NULL DEFAULT 'db' AFTER `encrypted_blob`;

CREATE TABLE IF NOT EXISTS `file_recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attachment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `encrypted_key` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_attachment_user` (`attachment_id`, `user_id`),
  KEY `idx_attachment_id` (`attachment_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_file_recipients_attachment` FOREIGN KEY (`attachment_id`) REFERENCES `tep_dinh_kem` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_file_recipients_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
