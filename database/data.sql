-- Tạo database đúng tên để code kết nối được
CREATE DATABASE IF NOT EXISTS `data` CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `data`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `tep_dinh_kem`;
DROP TABLE IF EXISTS `lich_su_trang_thai_cong_viec`;
DROP TABLE IF EXISTS `nhat_ky_truy_cap`;
DROP TABLE IF EXISTS `thanh_vien_du_an`;
DROP TABLE IF EXISTS `cong_viec`;
DROP TABLE IF EXISTS `cong_viec_phu_trach`;
DROP TABLE IF EXISTS `du_an`;
DROP TABLE IF EXISTS `tai_khoan`;
SET FOREIGN_KEY_CHECKS = 1;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 02, 2026 lúc 09:05 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Cơ sở dữ liệu: `data`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cong_viec`
--

CREATE TABLE `cong_viec` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('new','assigned','in_progress','blocked','submitted','approved','redo') NOT NULL DEFAULT 'new',
  `deadline` date NOT NULL,
  `start_date` date DEFAULT NULL,
  `assignee_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `expected_score` int(11) NOT NULL DEFAULT 0,
  `review_score` int(11) DEFAULT NULL,
  `review_comment` text DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cong_viec`
--

INSERT INTO `cong_viec` (`id`, `project_id`, `title`, `description`, `priority`, `status`, `deadline`, `start_date`, `assignee_id`, `created_by`, `expected_score`, `review_score`, `review_comment`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Thiết kế giao diện đăng nhập', 'Tạo giao diện đăng nhập theo vai trò Admin/Manager/Employee.', 'high', 'approved', '2026-03-10', '2026-03-02', 3, 2, 10, 8, '', '2026-04-02 22:38:18', '2026-04-02 14:49:05', '2026-04-02 22:38:18'),
(2, 1, 'Lập trình chức năng phân công việc', 'Xây dựng form phân công và lưu lịch sử giao việc.', 'high', 'approved', '2026-03-18', '2026-03-11', 3, 2, 15, 0, '', '2026-04-02 22:20:56', '2026-04-02 14:49:05', '2026-04-02 22:20:56'),
(3, 1, 'Xây dựng upload file mã hóa', 'Upload file kết quả, mã hóa trước khi lưu.', 'high', 'in_progress', '2026-03-20', '2026-03-12', 4, 2, 18, NULL, NULL, NULL, '2026-04-02 14:49:05', '2026-04-02 14:49:05'),
(4, 2, 'Báo cáo KPI theo nhân viên', 'Tính đúng hạn, trễ hạn, điểm review và xếp hạng KPI.', 'medium', 'redo', '2026-03-17', '2026-03-08', 3, 2, 12, 4, 'Cần bổ sung xử lý trường hợp công việc bị trả lại.', NULL, '2026-04-02 14:49:05', '2026-04-02 14:49:05'),
(5, 3, 'Xây dựng mô hình', 'jjjjjajdnaahodhqwdn', 'medium', 'new', '2026-04-10', NULL, NULL, 2, 10, NULL, NULL, NULL, '2026-04-02 15:56:36', '2026-04-02 15:56:36'),
(6, 4, 'làm màu', 'đâsdasdadas', 'medium', 'approved', '2026-04-04', '2026-04-03', 7, 2, 10, 0, '', '2026-04-03 01:32:21', '2026-04-03 01:31:38', '2026-04-03 01:32:21'),
(7, 4, 'làm biếng', 'adssadasd', 'medium', 'assigned', '2026-04-16', '2026-04-03', 4, 2, 10, NULL, NULL, NULL, '2026-04-03 01:33:08', '2026-04-03 01:36:19');



-- --------------------------------------------------------
--
-- Cấu trúc bảng cho bảng `cong_viec_phu_trach`
--

CREATE TABLE `cong_viec_phu_trach` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dữ liệu mẫu cho bảng `cong_viec_phu_trach`
--

INSERT INTO `cong_viec_phu_trach` (`id`, `task_id`, `user_id`, `created_at`) VALUES
(1, 1, 3, '2026-04-02 14:49:05'),
(2, 2, 3, '2026-04-02 14:49:05'),
(3, 3, 4, '2026-04-02 14:49:05'),
(4, 4, 3, '2026-04-02 14:49:05'),
(5, 6, 7, '2026-04-03 01:31:38'),
(6, 7, 4, '2026-04-03 01:36:19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `du_an`
--

CREATE TABLE `du_an` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('planning','active','completed') NOT NULL DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `du_an`
--

INSERT INTO `du_an` (`id`, `code`, `name`, `description`, `start_date`, `end_date`, `priority`, `status`, `created_by`, `created_at`) VALUES
(1, 'TTW-2026-001', 'Website giao việc nội bộ', 'Dự án demo theo đề tài môn học, có quản lý file mã hóa và KPI.', '2026-03-01', '2026-04-30', 'high', 'active', 2, '2026-04-02 14:49:05'),
(2, 'TTW-2026-002', 'Cải tiến dashboard KPI', 'Bổ sung thống kê KPI theo nhân sự và dự án.', '2026-03-05', '2026-05-15', 'medium', 'planning', 2, '2026-04-02 14:49:05'),
(3, 'TTW-2026-003', 'BITCOIN', 'xây dựng hệ thống vippromax', '2026-04-04', '2026-04-17', 'medium', 'planning', 2, '2026-04-02 15:55:40'),
(4, 'TTW-2026-004', 'abc', 'ádasdasda', '2026-04-08', '2026-04-29', 'medium', 'active', 2, '2026-04-02 22:40:53');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_trang_thai_cong_viec`
--

CREATE TABLE `lich_su_trang_thai_cong_viec` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_status` varchar(50) NOT NULL,
  `new_status` varchar(50) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_su_trang_thai_cong_viec`
--

INSERT INTO `lich_su_trang_thai_cong_viec` (`id`, `task_id`, `user_id`, `old_status`, `new_status`, `note`, `created_at`) VALUES
(1, 1, 2, 'new', 'assigned', 'Phân công cho nhân viên 1', '2026-04-02 14:49:05'),
(2, 1, 3, 'assigned', 'submitted', 'Đã hoàn thành và gửi duyệt', '2026-04-02 14:49:05'),
(3, 1, 2, 'submitted', 'approved', 'Duyệt đạt', '2026-04-02 14:49:05'),
(4, 2, 2, 'new', 'assigned', 'Phân công cho nhân viên 1', '2026-04-02 14:49:05'),
(5, 2, 3, 'assigned', 'submitted', 'Đã nộp chờ duyệt', '2026-04-02 14:49:05'),
(6, 3, 2, 'new', 'assigned', 'Phân công cho nhân viên 2', '2026-04-02 14:49:05'),
(7, 3, 4, 'assigned', 'in_progress', 'Đang lập trình và test', '2026-04-02 14:49:05'),
(8, 4, 2, 'submitted', 'redo', 'Yêu cầu bổ sung KPI', '2026-04-02 14:49:05'),
(9, 1, 1, 'assigned', 'approved', '', '2026-04-02 18:26:16'),
(10, 2, 2, 'submitted', 'approved', '', '2026-04-02 22:20:56'),
(11, 1, 2, 'approved', 'approved', '', '2026-04-02 22:38:04'),
(12, 1, 2, 'approved', 'approved', '', '2026-04-02 22:38:18'),
(13, 6, 2, 'assigned', 'approved', '', '2026-04-03 01:32:05'),
(14, 6, 2, 'approved', 'approved', '', '2026-04-03 01:32:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhat_ky_truy_cap`
--

CREATE TABLE `nhat_ky_truy_cap` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhat_ky_truy_cap`
--

INSERT INTO `nhat_ky_truy_cap` (`id`, `user_id`, `action`, `target_type`, `target_id`, `description`, `created_at`) VALUES
(1, 1, 'login', 'auth', NULL, 'Admin đăng nhập hệ thống', '2026-04-02 14:49:05'),
(2, 2, 'create', 'project', 1, 'Tạo dự án Website giao việc nội bộ', '2026-04-02 14:49:05'),
(3, 2, 'assign', 'task', 2, 'Phân công công việc cho nhân viên', '2026-04-02 14:49:05'),
(4, 3, 'update_status', 'task', 2, 'Nhân viên cập nhật trạng thái submitted', '2026-04-02 14:49:05'),
(5, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 15:31:54'),
(6, 1, 'assign', 'task', 1, 'Phân công công việc', '2026-04-02 15:37:12'),
(7, 1, 'toggle', 'user', 3, 'Khóa/mở khóa tài khoản', '2026-04-02 15:48:32'),
(8, 1, 'toggle', 'user', 3, 'Khóa/mở khóa tài khoản', '2026-04-02 15:48:34'),
(9, 1, 'toggle', 'user', 3, 'Khóa/mở khóa tài khoản', '2026-04-02 15:48:36'),
(10, 1, 'toggle', 'user', 3, 'Khóa/mở khóa tài khoản', '2026-04-02 15:48:39'),
(11, 1, 'toggle', 'user', 1, 'Khóa/mở khóa tài khoản', '2026-04-02 15:48:46'),
(12, 1, 'toggle', 'user', 1, 'Khóa/mở khóa tài khoản', '2026-04-02 15:48:50'),
(13, 1, 'toggle', 'user', 2, 'Khóa/mở khóa tài khoản', '2026-04-02 15:48:52'),
(14, 1, 'toggle', 'user', 2, 'Khóa/mở khóa tài khoản', '2026-04-02 15:48:52'),
(15, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 15:52:56'),
(16, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 15:53:08'),
(17, 2, 'create', 'project', 3, 'Tạo dự án mới', '2026-04-02 15:55:40'),
(18, 2, 'create', 'task', 5, 'Tạo công việc', '2026-04-02 15:56:36'),
(19, 2, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 15:57:27'),
(20, 3, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 15:57:40'),
(21, 3, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:01:22'),
(22, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:01:29'),
(23, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:01:54'),
(24, 3, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:02:27'),
(25, 3, 'upload', 'attachment', 2, 'Upload file công việc', '2026-04-02 18:03:44'),
(26, 3, 'download', 'attachment', 1, 'Tải file công việc', '2026-04-02 18:03:53'),
(27, 3, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:04:41'),
(28, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:04:54'),
(29, 1, 'create', 'user', NULL, 'Tạo tài khoản mới: nva@gmail.com', '2026-04-02 18:06:15'),
(30, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:06:19'),
(31, 5, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:06:27'),
(32, 5, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:06:46'),
(33, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:06:48'),
(34, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:08:57'),
(35, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:09:11'),
(36, 2, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:19:53'),
(37, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:19:59'),
(38, 1, 'create', 'user', NULL, 'Tạo tài khoản mới: abc@gmail.com', '2026-04-02 18:20:28'),
(39, 1, 'update', 'project', 3, 'Cập nhật dự án', '2026-04-02 18:21:12'),
(40, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:22:45'),
(41, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:22:55'),
(42, 2, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:24:22'),
(43, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:24:27'),
(44, 1, 'review', 'task', 1, 'Duyệt và đánh giá công việc', '2026-04-02 18:26:16'),
(45, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:27:21'),
(46, 6, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 18:27:28'),
(47, 6, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 18:27:39'),
(48, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 19:00:57'),
(49, 1, 'create', 'project_member', 1, 'Thêm thành viên vào dự án', '2026-04-02 19:03:57'),
(50, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 19:04:01'),
(51, 6, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 19:04:08'),
(52, 6, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 21:40:57'),
(53, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 21:42:09'),
(54, 2, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 21:44:29'),
(55, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 21:44:45'),
(56, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 22:07:52'),
(57, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 22:08:01'),
(58, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-02 22:10:27'),
(59, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-02 22:11:40'),
(60, 2, 'review', 'task', 2, 'Duyệt và đánh giá công việc', '2026-04-02 22:20:56'),
(61, 2, 'upload', 'attachment', 2, 'Upload file hướng dẫn (Manager): tài liệu tham khảo', '2026-04-02 22:20:56'),
(62, 2, 'download', 'attachment', 2, 'Tải file công việc', '2026-04-02 22:33:05'),
(63, 2, 'review', 'task', 1, 'Duyệt và đánh giá công việc', '2026-04-02 22:38:04'),
(64, 2, 'review', 'task', 1, 'Duyệt và đánh giá công việc', '2026-04-02 22:38:18'),
(65, 2, 'upload', 'attachment', 1, 'Upload file hướng dẫn (Manager): ', '2026-04-02 22:38:18'),
(66, 2, 'create', 'project', 4, 'Tạo dự án mới', '2026-04-02 22:40:53'),
(67, 2, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:14:17'),
(68, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:14:30'),
(69, 2, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:27:56'),
(70, 1, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:27:58'),
(71, 1, 'create', 'user', NULL, 'Tạo tài khoản mới: nv1@gmail.com', '2026-04-03 01:28:33'),
(72, 1, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:28:40'),
(73, 7, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:28:47'),
(74, 7, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:28:57'),
(75, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:29:16'),
(76, 2, 'create', 'project_member', 4, 'Thêm thành viên vào dự án', '2026-04-03 01:29:32'),
(77, 2, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:29:43'),
(78, 7, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:29:54'),
(79, 7, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:30:19'),
(80, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:30:28'),
(81, 2, 'create', 'task', 6, 'Tạo công việc', '2026-04-03 01:31:38'),
(82, 2, 'assign', 'task', 6, 'Phân công công việc', '2026-04-03 01:31:54'),
(83, 2, 'review', 'task', 6, 'Duyệt và đánh giá công việc', '2026-04-03 01:32:05'),
(84, 2, 'review', 'task', 6, 'Duyệt và đánh giá công việc', '2026-04-03 01:32:21'),
(85, 2, 'upload', 'attachment', 6, 'Upload file hướng dẫn (Manager): ', '2026-04-03 01:32:21'),
(86, 2, 'create', 'project_member', 4, 'Thêm thành viên vào dự án', '2026-04-03 01:32:44'),
(87, 2, 'create', 'task', 7, 'Tạo công việc', '2026-04-03 01:33:08'),
(88, 2, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:33:22'),
(89, 3, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:33:32'),
(90, 3, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:34:44'),
(91, 4, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:34:57'),
(92, 4, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:35:08'),
(93, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:35:17'),
(94, 2, 'assign', 'task', 7, 'Phân công công việc', '2026-04-03 01:36:19'),
(95, 2, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 01:36:48'),
(96, 4, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 01:36:59'),
(97, 4, 'logout', 'auth', NULL, 'Đăng xuất hệ thống', '2026-04-03 02:01:22'),
(98, 2, 'login', 'auth', NULL, 'Đăng nhập hệ thống', '2026-04-03 02:01:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','employee') NOT NULL DEFAULT 'employee',
  `department` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tai_khoan`
--

INSERT INTO `tai_khoan` (`id`, `name`, `email`, `password`, `role`, `department`, `status`, `created_at`) VALUES
(1, 'Nguyen Admin', 'admin@taskflow.local', '$2y$12$ZTAdewvUJLPqmSxU6o3lROMlEjs7mfWX.EAySFNB3VBI9goD2npf2', 'admin', 'Ban CNTT', 'active', '2026-04-02 14:49:05'),
(2, 'Tran Quan Ly', 'manager@taskflow.local', '$2y$12$xxANoFxi4jMPl1ANwVf5WeuJ.fq3GTynS46wxUuyj7B1uCAEMBA86', 'manager', 'PMO', 'active', '2026-04-02 14:49:05'),
(3, 'Le Nhan Vien 1', 'employee1@taskflow.local', '$2y$12$4b1dzmsaU4obe86xP/Uybu8R0xaXghTHdD/gRNB4WWFf9b48mCVJe', 'employee', 'Kỹ thuật', 'active', '2026-04-02 14:49:05'),
(4, 'Pham Nhan Vien 2', 'employee2@taskflow.local', '$2y$12$vKjlixWa1n9xltdBeB1zYeQLhE01VNGJ2Tul.pDTP32OLdIzGePf6', 'employee', 'Thiết kế', 'active', '2026-04-02 14:49:05'),
(5, 'nva', 'nva@gmail.com', '$2y$10$N5/kqv21.8pRtq5NeglN3.fE8uIYbQh.byr7QR1CdMpWUqMFigFEe', 'employee', 'AF', 'active', '2026-04-02 18:06:15'),
(6, 'abc', 'abc@gmail.com', '$2y$10$LfbspEBFWs60P0xB9v9Q5O21M0x.Ld5p3gQo8a.PjQbjSmLpMShjq', 'manager', 'AF', 'active', '2026-04-02 18:20:28'),
(7, 'nv1', 'nv1@gmail.com', '$2y$10$SuGN/R6g0W8qbUDS56Al1uXHdGTw.hAq5gynkT282MXeS8v6TmGRO', 'employee', 'SCM', 'active', '2026-04-03 01:28:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tep_dinh_kem`
--

CREATE TABLE `tep_dinh_kem` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(150) DEFAULT NULL,
  `file_size` int(11) NOT NULL,
  `encrypted_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `file_type` varchar(20) DEFAULT 'employee',
  `upload_reason` varchar(255) DEFAULT NULL,
  `encrypted_key` longtext DEFAULT NULL COMMENT 'Khóa AES được bọc bằng RSA (base64)',
  `iv` varchar(255) DEFAULT NULL COMMENT 'IV cho AES-256-CBC (base64)',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tep_dinh_kem`
--

INSERT INTO `tep_dinh_kem` (`id`, `task_id`, `original_name`, `stored_name`, `mime_type`, `file_size`, `encrypted_path`, `uploaded_by`, `file_type`, `upload_reason`, `created_at`) VALUES
(1, 2, 'Baigiuaky-Nhóm05.docx', 'enc_69ce4d108b2ef4.74023625.bin', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 3335383, 'C:\\xampp\\htdocs\\NgocToan_VanTrung_Web-app-giao-viec/storage/encrypted/enc_69ce4d108b2ef4.74023625.bin', 3, 'employee', NULL, '2026-04-02 18:03:44'),
(2, 2, 'slot_05_BigDataFundamentals.pdf', 'enc_69ce89586496f3.39416014.bin', 'application/pdf', 5022285, 'C:\\xampp\\htdocs\\NgocToan_VanTrung_Web-app-giao-viec/storage/encrypted/enc_69ce89586496f3.39416014.bin', 2, 'manager', 'tài liệu tham khảo', '2026-04-02 22:20:56'),
(3, 1, 'Báo cáo thực hành tuần 4 - Nhóm 4.docx', 'enc_69ce8d6add95e2.41854291.bin', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 2232903, 'C:\\xampp\\htdocs\\NgocToan_VanTrung_Web-app-giao-viec/storage/encrypted/enc_69ce8d6add95e2.41854291.bin', 2, 'manager', NULL, '2026-04-02 22:38:18'),
(4, 6, 'slot_03_Distributed_System_1.pdf', 'enc_69ceb635c64c19.15977958.bin', 'application/pdf', 2288817, 'C:\\xampp\\htdocs\\NgocToan_VanTrung_Web-app-giao-viec/storage/encrypted/enc_69ceb635c64c19.15977958.bin', 2, 'manager', NULL, '2026-04-03 01:32:21');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanh_vien_du_an`
--

CREATE TABLE `thanh_vien_du_an` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_role` enum('lead','member','reviewer') NOT NULL DEFAULT 'member',
  `joined_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thanh_vien_du_an`
--

INSERT INTO `thanh_vien_du_an` (`id`, `project_id`, `user_id`, `project_role`, `joined_at`) VALUES
(1, 1, 2, 'lead', '2026-04-02 14:49:05'),
(2, 1, 3, 'member', '2026-04-02 14:49:05'),
(3, 1, 4, 'member', '2026-04-02 14:49:05'),
(4, 2, 2, 'lead', '2026-04-02 14:49:05'),
(5, 2, 3, 'member', '2026-04-02 14:49:05'),
(6, 3, 2, 'lead', '2026-04-02 15:55:40'),
(7, 1, 6, 'member', '2026-04-02 19:03:57'),
(8, 4, 2, 'lead', '2026-04-02 22:40:53'),
(9, 4, 7, 'member', '2026-04-03 01:29:32'),
(10, 4, 4, 'member', '2026-04-03 01:32:44');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cong_viec`
--
ALTER TABLE `cong_viec`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cong_viec_project` (`project_id`),
  ADD KEY `fk_cong_viec_assignee` (`assignee_id`),
  ADD KEY `fk_cong_viec_created_by` (`created_by`);

--
-- Chỉ mục cho bảng `cong_viec_phu_trach`
--
ALTER TABLE `cong_viec_phu_trach`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_task_user` (`task_id`,`user_id`),
  ADD KEY `idx_cpt_task` (`task_id`),
  ADD KEY `idx_cpt_user` (`user_id`);

--
-- Chỉ mục cho bảng `du_an`
--
ALTER TABLE `du_an`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_du_an_created_by` (`created_by`);

--
-- Chỉ mục cho bảng `lich_su_trang_thai_cong_viec`
--
ALTER TABLE `lich_su_trang_thai_cong_viec`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_task_logs_task` (`task_id`),
  ADD KEY `fk_task_logs_user` (`user_id`);

--
-- Chỉ mục cho bảng `nhat_ky_truy_cap`
--
ALTER TABLE `nhat_ky_truy_cap`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_nhat_ky_truy_cap_user` (`user_id`);

--
-- Chỉ mục cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `tep_dinh_kem`
--
ALTER TABLE `tep_dinh_kem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tep_dinh_kem_task` (`task_id`),
  ADD KEY `fk_tep_dinh_kem_user` (`uploaded_by`);

--
-- Chỉ mục cho bảng `thanh_vien_du_an`
--
ALTER TABLE `thanh_vien_du_an`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_project_member` (`project_id`,`user_id`),
  ADD KEY `fk_thanh_vien_du_an_user` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cong_viec`
--
ALTER TABLE `cong_viec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `cong_viec_phu_trach`
--
ALTER TABLE `cong_viec_phu_trach`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `du_an`
--
ALTER TABLE `du_an`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `lich_su_trang_thai_cong_viec`
--
ALTER TABLE `lich_su_trang_thai_cong_viec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `nhat_ky_truy_cap`
--
ALTER TABLE `nhat_ky_truy_cap`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT cho bảng `tai_khoan`
--
ALTER TABLE `tai_khoan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `tep_dinh_kem`
--
ALTER TABLE `tep_dinh_kem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `thanh_vien_du_an`
--
ALTER TABLE `thanh_vien_du_an`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cong_viec`
--
ALTER TABLE `cong_viec`
  ADD CONSTRAINT `fk_cong_viec_assignee` FOREIGN KEY (`assignee_id`) REFERENCES `tai_khoan` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cong_viec_created_by` FOREIGN KEY (`created_by`) REFERENCES `tai_khoan` (`id`),
  ADD CONSTRAINT `fk_cong_viec_project` FOREIGN KEY (`project_id`) REFERENCES `du_an` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `cong_viec_phu_trach`
--
ALTER TABLE `cong_viec_phu_trach`
  ADD CONSTRAINT `fk_cpt_task` FOREIGN KEY (`task_id`) REFERENCES `cong_viec` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cpt_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `du_an`
--
ALTER TABLE `du_an`
  ADD CONSTRAINT `fk_du_an_created_by` FOREIGN KEY (`created_by`) REFERENCES `tai_khoan` (`id`);

--
-- Các ràng buộc cho bảng `lich_su_trang_thai_cong_viec`
--
ALTER TABLE `lich_su_trang_thai_cong_viec`
  ADD CONSTRAINT `fk_task_logs_task` FOREIGN KEY (`task_id`) REFERENCES `cong_viec` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_task_logs_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nhat_ky_truy_cap`
--
ALTER TABLE `nhat_ky_truy_cap`
  ADD CONSTRAINT `fk_nhat_ky_truy_cap_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `tep_dinh_kem`
--
ALTER TABLE `tep_dinh_kem`
  ADD CONSTRAINT `fk_tep_dinh_kem_task` FOREIGN KEY (`task_id`) REFERENCES `cong_viec` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tep_dinh_kem_user` FOREIGN KEY (`uploaded_by`) REFERENCES `tai_khoan` (`id`);

--
-- Các ràng buộc cho bảng `thanh_vien_du_an`
--
ALTER TABLE `thanh_vien_du_an`
  ADD CONSTRAINT `fk_thanh_vien_du_an_project` FOREIGN KEY (`project_id`) REFERENCES `du_an` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_thanh_vien_du_an_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
