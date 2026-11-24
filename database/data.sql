-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: data
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `data`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `data` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `data`;

--
-- Table structure for table `cong_viec`
--

DROP TABLE IF EXISTS `cong_viec`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cong_viec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('new','assigned','in_progress','blocked','submitted','approved','redo') NOT NULL DEFAULT 'new',
  `deadline` datetime NOT NULL,
  `start_date` date DEFAULT NULL,
  `assignee_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `expected_score` int(11) NOT NULL DEFAULT 0,
  `review_score` int(11) DEFAULT NULL,
  `review_comment` text DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cong_viec_project` (`project_id`),
  KEY `fk_cong_viec_assignee` (`assignee_id`),
  KEY `fk_cong_viec_created_by` (`created_by`),
  CONSTRAINT `fk_cong_viec_assignee` FOREIGN KEY (`assignee_id`) REFERENCES `tai_khoan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_cong_viec_created_by` FOREIGN KEY (`created_by`) REFERENCES `tai_khoan` (`id`),
  CONSTRAINT `fk_cong_viec_project` FOREIGN KEY (`project_id`) REFERENCES `du_an` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cong_viec`
--

LOCK TABLES `cong_viec` WRITE;
/*!40000 ALTER TABLE `cong_viec` DISABLE KEYS */;
INSERT INTO `cong_viec` VALUES (1,1,'Khảo sát nghiệp vụ & đặc tả yêu cầu CRM 2.0','Thu thập yêu cầu từ phòng Kinh doanh và CSKH, xây dựng tài liệu SRS và sơ đồ luồng nghiệp vụ khách hàng.','high','approved','2026-08-15 17:00:00','2026-08-03',4,2,95,94,'Tài liệu đặc tả rõ ràng, đầy đủ biểu đồ luồng và tiêu chí nghiệm thu.','2026-08-14 16:20:00','2026-08-02 09:00:00','2026-08-14 16:20:00'),(2,1,'Thiết kế giao diện UI/UX màn hình quản lý khách hàng','Thiết kế wireframe và prototype Figma cho danh sách khách hàng, chi tiết lịch sử chăm sóc và pipeline bán hàng.','high','approved','2026-08-30 17:00:00','2026-08-16',3,2,90,92,'Giao diện trực quan, tối ưu thao tác cho nhân viên kinh doanh.','2026-08-29 15:40:00','2026-08-15 09:00:00','2026-08-29 15:40:00'),(3,1,'Xây dựng API quản lý Lead & Cơ hội bán hàng','Phát triển RESTful API CRUD cho Lead, phân bổ khách hàng tiềm năng, chuyển đổi trạng thái và ghi nhận lịch sử gọi.','high','approved','2026-09-20 17:00:00','2026-09-01',5,2,95,90,'API ổn định, phản hồi nhanh và có đầy đủ kiểm tra phân quyền.','2026-09-19 16:50:00','2026-08-31 09:00:00','2026-09-19 16:50:00'),(4,1,'Tích hợp đồng bộ lịch sử email & tổng đài CSKH','Kết nối webhook tổng đài và máy chủ SMTP/IMAP để tự động lưu lịch sử trao đổi vào hồ sơ khách hàng.','medium','approved','2026-10-08 17:00:00','2026-09-21',4,2,85,82,'Hoàn thành đúng yêu cầu kỹ thuật, trễ 2 ngày do phía đối tác tổng đài đổi API.','2026-10-10 11:15:00','2026-09-20 09:00:00','2026-10-10 11:15:00'),(5,1,'Kiểm thử hệ thống (UAT) & bàn giao vận hành CRM 2.0','Thực hiện kiểm thử tích hợp, kiểm thử chấp nhận người dùng cùng đội kinh doanh và lập biên bản bàn giao.','high','approved','2026-10-28 17:00:00','2026-10-12',3,2,100,96,'Toàn bộ 42 kịch bản UAT đều đạt, hệ thống đã go-live ổn định.','2026-10-27 16:30:00','2026-10-11 09:00:00','2026-10-27 16:30:00'),(6,2,'Thiết kế kiến trúc cơ sở dữ liệu sản phẩm & tồn kho đa kho','Thiết kế lược đồ CSDL hỗ trợ nhiều biến thể sản phẩm, giá theo kênh bán và đồng bộ tồn kho theo chi nhánh.','high','approved','2026-10-02 17:00:00','2026-09-16',10,11,95,93,'Thiết kế chuẩn hóa tốt, đáp ứng bài toán đa kho và mở rộng sau này.','2026-10-01 16:10:00','2026-09-15 09:00:00','2026-10-01 16:10:00'),(7,2,'Phát triển giỏ hàng & luồng thanh toán trực tuyến','Xây dựng luồng giỏ hàng, áp dụng mã giảm giá, tính phí vận chuyển và tích hợp cổng thanh toán VNPay/MoMo.','high','approved','2026-10-28 17:00:00','2026-10-05',9,11,95,91,'Thanh toán sandbox và xử lý IPN callback hoạt động chính xác.','2026-10-27 17:00:00','2026-10-04 09:00:00','2026-10-27 17:00:00'),(8,2,'Xây dựng bộ kết nối đồng bộ đơn hàng Shopee & Lazada','Lập trình worker kéo đơn hàng tự động mỗi 5 phút, cập nhật trạng thái giao vận và trừ tồn kho tập trung.','high','submitted','2026-11-26 17:00:00','2026-11-01',7,11,90,NULL,NULL,NULL,'2026-10-31 09:00:00','2026-11-03 09:00:00'),(9,2,'Tối ưu tốc độ tải trang danh mục & tìm kiếm sản phẩm','Áp dụng cache Redis, lazy-load hình ảnh và tối ưu chỉ mục tìm kiếm toàn văn cho 50.000 SKU.','medium','in_progress','2026-12-03 17:00:00','2026-11-12',7,11,85,NULL,NULL,NULL,'2026-11-11 09:00:00','2026-11-14 09:00:00'),(10,2,'Xây dựng module xuất hóa đơn điện tử tự động','Tích hợp API nhà cung cấp hóa đơn điện tử để phát hành hóa đơn ngay khi đơn hàng chuyển sang trạng thái hoàn tất.','medium','redo','2026-11-22 17:00:00','2026-11-02',4,11,90,58,'Cần bổ sung xử lý trường hợp hủy hóa đơn khi khách trả hàng một phần và ghi log lỗi chi tiết hơn.',NULL,'2026-11-01 09:00:00','2026-11-04 09:00:00'),(11,2,'Kiểm thử tải (Load Testing) trước đợt khuyến mãi cuối năm','Chạy kịch bản mô phỏng 2.000 người dùng đồng thời bằng JMeter/k6 và đề xuất phương án cân bằng tải.','high','assigned','2026-12-12 17:00:00','2026-11-25',3,11,95,NULL,NULL,NULL,'2026-11-24 09:00:00','2026-11-24 17:30:00'),(12,2,'Soạn tài liệu hướng dẫn vận hành cho đội Kho & CSKH','Biên soạn tài liệu sử dụng hệ thống xử lý đơn đa kênh và kịch bản xử lý sự cố thường gặp.','low','new','2026-12-18 17:00:00',NULL,NULL,11,80,NULL,NULL,NULL,'2026-09-18 08:30:00','2026-09-21 08:30:00'),(13,3,'Thiết kế bộ Design System & UI các màn hình chính Mobile App','Hoàn thiện bộ component chuẩn iOS/Android, màn hình Trang chủ, Tra cứu đơn hàng, Điểm thưởng và Hồ sơ cá nhân.','high','approved','2026-10-20 17:00:00','2026-10-02',5,6,90,95,'Thiết kế hiện đại, tuân thủ tốt hướng dẫn Human Interface và Material Design.','2026-10-19 15:00:00','2026-10-01 09:00:00','2026-10-19 15:00:00'),(14,3,'Phát triển chức năng đăng nhập OTP & định danh sinh trắc học','Triển khai đăng nhập bằng số điện thoại OTP, hỗ trợ FaceID/TouchID và làm mới phiên đăng nhập an toàn.','high','approved','2026-11-08 17:00:00','2026-10-21',5,6,95,89,'Hoạt động mượt trên cả iOS và Android, xử lý tốt giới hạn tần suất gửi OTP.','2026-11-07 16:45:00','2026-10-20 09:00:00','2026-11-07 16:45:00'),(15,3,'Xây dựng hệ thống thông báo đẩy (Push Notification) theo phân khúc','Tích hợp Firebase Cloud Messaging (FCM) và APNs để gửi thông báo trạng thái đơn hàng và ưu đãi cá nhân hóa.','medium','submitted','2026-11-25 17:00:00','2026-11-05',4,6,85,NULL,NULL,NULL,'2026-11-04 09:00:00','2026-11-07 09:00:00'),(16,3,'Phát triển màn hình tích điểm & đổi voucher thành viên','Hiển thị hạng thẻ thành viên, lịch sử tích/tiêu điểm và quét mã QR đổi ưu đãi tại cửa hàng.','medium','in_progress','2026-12-08 17:00:00','2026-11-14',7,6,90,NULL,NULL,NULL,'2026-11-13 09:00:00','2026-11-16 09:00:00'),(17,3,'Tích hợp khung chat trực tuyến với bộ phận hỗ trợ khách hàng','Kết nối WebSocket hỗ trợ gửi tin nhắn văn bản, hình ảnh sản phẩm và chuyển tiếp hội thoại cho nhân viên CSKH.','high','blocked','2026-12-05 17:00:00','2026-11-10',7,6,90,NULL,NULL,NULL,'2026-11-09 09:00:00','2026-11-12 09:00:00'),(18,3,'Kiểm thử tương thích thiết bị & chuẩn bị phát hành App Store / Play Store','Kiểm thử trên các dòng máy phổ biến, hoàn thiện bộ ảnh chụp màn hình và hồ sơ xét duyệt cửa hàng ứng dụng.','medium','assigned','2027-01-10 17:00:00','2026-12-10',10,6,95,NULL,NULL,NULL,'2026-11-22 10:00:00','2026-11-24 17:30:00'),(19,4,'Chuẩn hóa nguồn dữ liệu & xây dựng pipeline ETL tự động hằng đêm','Trích xuất dữ liệu từ CRM, ERP và TMĐT, làm sạch dữ liệu trùng lặp và nạp định kỳ vào kho dữ liệu phân tích.','high','approved','2026-11-05 17:00:00','2026-10-16',3,2,95,92,'Pipeline chạy ổn định, thời gian xử lý giảm 40% so với bản thử nghiệm.','2026-11-04 16:00:00','2026-10-15 09:00:00','2026-11-04 16:00:00'),(20,4,'Xây dựng bộ chỉ số KPI điều hành theo phòng ban và dự án','Định nghĩa công thức đo lường tỷ lệ đúng hạn, điểm chất lượng công việc và hiệu suất nhân sự theo tháng/quý.','high','approved','2026-11-18 17:00:00','2026-11-01',5,2,90,86,'Chỉ số chuẩn xác, đã đối chiếu khớp với báo cáo tài chính và PMO.','2026-11-19 10:30:00','2026-10-31 09:00:00','2026-11-19 10:30:00'),(21,4,'Thiết kế Dashboard trực quan hóa doanh thu & tỷ lệ chuyển đổi','Xây dựng biểu đồ xu hướng doanh thu, phễu chuyển đổi và bộ lọc động theo khu vực, kênh bán và thời gian.','medium','in_progress','2026-12-06 17:00:00','2026-11-15',7,2,85,NULL,NULL,NULL,'2026-11-14 09:00:00','2026-11-17 09:00:00'),(22,4,'Thiết lập cơ chế xuất báo cáo PDF/Excel định kỳ gửi Ban Giám đốc','Tự động tổng hợp báo cáo tuần/tháng vào 08:00 sáng thứ Hai và gửi email kèm tệp đính kèm có phân quyền xem.','medium','submitted','2026-11-27 17:00:00','2026-11-08',3,2,85,NULL,NULL,NULL,'2026-11-07 09:00:00','2026-11-10 09:00:00'),(23,4,'Kiểm định độ chính xác dữ liệu đối soát giữa ERP và kho BI','Lập bộ truy vấn đối chiếu tự động phát hiện sai lệch số liệu và gửi cảnh báo khi chênh lệch vượt 0.1%.','high','assigned','2026-12-18 17:00:00','2026-11-28',9,2,90,NULL,NULL,NULL,'2026-11-22 10:00:00','2026-11-24 17:30:00'),(24,5,'Rà soát lỗ hổng bảo mật OWASP Top 10 cho các ứng dụng nội bộ','Thực hiện quét bảo mật tĩnh/động, kiểm tra CSRF, XSS, SQL Injection và phân quyền truy cập trên toàn bộ hệ thống.','high','in_progress','2026-12-10 17:00:00','2026-11-21',4,11,95,NULL,NULL,NULL,'2026-11-20 09:00:00','2026-11-23 09:00:00'),(25,5,'Chuẩn hóa chính sách mã hóa tệp đính kèm E2E & quản lý khóa RSA','Hoàn thiện quy trình xoay vòng khóa định kỳ, sao lưu khóa khôi phục an toàn và kiểm thử giải mã đa người nhận.','high','assigned','2026-12-18 17:00:00','2026-11-26',4,11,95,NULL,NULL,NULL,'2026-11-22 10:00:00','2026-11-24 17:30:00'),(26,5,'Thiết lập hệ thống giám sát nhật ký truy cập & cảnh báo bất thường','Cấu hình lưu vết tập trung cho các thao tác nhạy cảm (đăng nhập sai nhiều lần, tải tài liệu mật, đổi quyền).','medium','assigned','2026-12-28 17:00:00','2026-12-01',3,11,85,NULL,NULL,NULL,'2026-11-22 10:00:00','2026-11-24 17:30:00'),(27,5,'Soạn thảo bộ quy trình kiểm soát an toàn thông tin theo ISO 27001','Xây dựng chính sách bảo mật thông tin, quy trình ứng cứu sự cố và kế hoạch đào tạo nhận thức an toàn thông tin.','medium','new','2027-01-20 17:00:00',NULL,NULL,11,85,NULL,NULL,NULL,'2026-11-23 09:30:00','2026-11-24 17:30:00'),(28,5,'Diễn tập sao lưu & phục hồi thảm họa (DR Drill) toàn hệ thống','Thực hiện kịch bản khôi phục cơ sở dữ liệu và dịch vụ ứng dụng trên môi trường dự phòng, đo thời gian RTO/RPO.','low','new','2027-02-15 17:00:00',NULL,NULL,11,90,NULL,NULL,NULL,'2026-11-24 09:30:00','2026-11-24 17:30:00');
/*!40000 ALTER TABLE `cong_viec` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cong_viec_phu_trach`
--

DROP TABLE IF EXISTS `cong_viec_phu_trach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cong_viec_phu_trach` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('assigned','in_progress','blocked','submitted','redo') NOT NULL DEFAULT 'assigned',
  `note` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_task_user` (`task_id`,`user_id`),
  KEY `idx_cpt_task` (`task_id`),
  KEY `idx_cpt_user` (`user_id`),
  CONSTRAINT `fk_cpt_task` FOREIGN KEY (`task_id`) REFERENCES `cong_viec` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cpt_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cong_viec_phu_trach`
--

LOCK TABLES `cong_viec_phu_trach` WRITE;
/*!40000 ALTER TABLE `cong_viec_phu_trach` DISABLE KEYS */;
INSERT INTO `cong_viec_phu_trach` VALUES (1,1,4,'assigned',NULL,NULL,'2026-08-02 09:00:00','0000-00-00 00:00:00'),(2,1,9,'assigned',NULL,NULL,'2026-08-02 09:00:00','0000-00-00 00:00:00'),(3,2,3,'assigned',NULL,NULL,'2026-08-15 09:00:00','0000-00-00 00:00:00'),(4,3,5,'assigned',NULL,NULL,'2026-08-31 09:00:00','0000-00-00 00:00:00'),(5,3,7,'assigned',NULL,NULL,'2026-08-31 09:00:00','0000-00-00 00:00:00'),(6,4,4,'assigned',NULL,NULL,'2026-09-20 09:00:00','0000-00-00 00:00:00'),(7,5,3,'assigned',NULL,NULL,'2026-10-11 09:00:00','0000-00-00 00:00:00'),(8,5,5,'assigned',NULL,NULL,'2026-10-11 09:00:00','0000-00-00 00:00:00'),(9,6,10,'assigned',NULL,NULL,'2026-09-15 09:00:00','0000-00-00 00:00:00'),(10,7,9,'assigned',NULL,NULL,'2026-10-04 09:00:00','0000-00-00 00:00:00'),(11,7,10,'assigned',NULL,NULL,'2026-10-04 09:00:00','0000-00-00 00:00:00'),(12,8,7,'assigned',NULL,NULL,'2026-10-31 09:00:00','0000-00-00 00:00:00'),(13,8,10,'assigned',NULL,NULL,'2026-10-31 09:00:00','0000-00-00 00:00:00'),(14,9,7,'assigned',NULL,NULL,'2026-11-11 09:00:00','0000-00-00 00:00:00'),(15,10,4,'assigned',NULL,NULL,'2026-11-01 09:00:00','0000-00-00 00:00:00'),(16,11,3,'assigned',NULL,NULL,'2026-11-24 09:00:00','0000-00-00 00:00:00'),(17,11,9,'assigned',NULL,NULL,'2026-11-24 09:00:00','0000-00-00 00:00:00'),(18,13,5,'assigned',NULL,NULL,'2026-10-01 09:00:00','0000-00-00 00:00:00'),(19,14,5,'assigned',NULL,NULL,'2026-10-20 09:00:00','0000-00-00 00:00:00'),(20,14,9,'assigned',NULL,NULL,'2026-10-20 09:00:00','0000-00-00 00:00:00'),(21,15,4,'assigned',NULL,NULL,'2026-11-04 09:00:00','0000-00-00 00:00:00'),(22,16,7,'assigned',NULL,NULL,'2026-11-13 09:00:00','0000-00-00 00:00:00'),(23,16,10,'assigned',NULL,NULL,'2026-11-13 09:00:00','0000-00-00 00:00:00'),(24,17,7,'assigned',NULL,NULL,'2026-11-09 09:00:00','0000-00-00 00:00:00'),(25,18,10,'assigned',NULL,NULL,'2026-11-22 10:00:00','0000-00-00 00:00:00'),(26,19,3,'assigned',NULL,NULL,'2026-10-15 09:00:00','0000-00-00 00:00:00'),(27,19,9,'assigned',NULL,NULL,'2026-10-15 09:00:00','0000-00-00 00:00:00'),(28,20,5,'assigned',NULL,NULL,'2026-10-31 09:00:00','0000-00-00 00:00:00'),(29,21,7,'assigned',NULL,NULL,'2026-11-14 09:00:00','0000-00-00 00:00:00'),(30,22,3,'assigned',NULL,NULL,'2026-11-07 09:00:00','0000-00-00 00:00:00'),(31,23,9,'assigned',NULL,NULL,'2026-11-22 10:00:00','0000-00-00 00:00:00'),(32,23,10,'assigned',NULL,NULL,'2026-11-22 10:00:00','0000-00-00 00:00:00'),(33,24,4,'assigned',NULL,NULL,'2026-11-20 09:00:00','0000-00-00 00:00:00'),(34,24,3,'assigned',NULL,NULL,'2026-11-20 09:00:00','0000-00-00 00:00:00'),(35,25,4,'assigned',NULL,NULL,'2026-11-22 10:00:00','0000-00-00 00:00:00'),(36,26,3,'assigned',NULL,NULL,'2026-11-22 10:00:00','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `cong_viec_phu_trach` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `du_an`
--

DROP TABLE IF EXISTS `du_an`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `du_an` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('planning','active','completed') NOT NULL DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_du_an_created_by` (`created_by`),
  CONSTRAINT `fk_du_an_created_by` FOREIGN KEY (`created_by`) REFERENCES `tai_khoan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `du_an`
--

LOCK TABLES `du_an` WRITE;
/*!40000 ALTER TABLE `du_an` DISABLE KEYS */;
INSERT INTO `du_an` VALUES (1,'TTW-2026-001','Hệ thống Quản trị Quan hệ Khách hàng (CRM 2.0)','Nâng cấp toàn diện nền tảng CRM nội bộ: quản lý phễu bán hàng, chăm sóc khách hàng đa kênh, đồng bộ lịch sử tương tác và báo cáo doanh thu theo thời gian thực.','2026-08-01','2026-10-31','high','completed',2,'2026-07-28 09:00:00'),(2,'TTW-2026-002','Nền tảng Thương mại Điện tử OmniChannel','Xây dựng hệ thống bán hàng hợp kênh tích hợp Website, sàn TMĐT, quản lý tồn kho tập trung, giỏ hàng thông minh và cổng thanh toán tự động.','2026-09-15','2026-12-20','high','active',11,'2026-09-10 08:30:00'),(3,'TTW-2026-003','Ứng dụng Di động Chăm sóc Khách hàng (Mobile App)','Phát triển ứng dụng iOS/Android giúp khách hàng theo dõi đơn hàng, tích điểm thành viên, nhận thông báo khuyến mãi và gửi yêu cầu hỗ trợ trực tuyến.','2026-10-01','2027-01-15','high','active',6,'2026-09-25 10:15:00'),(4,'TTW-2026-004','Hệ thống Phân tích Dữ liệu & Báo cáo BI Tự động','Thiết lập kho dữ liệu tập trung (Data Warehouse), pipeline ETL tự động và bộ dashboard điều hành theo dõi KPI doanh số, vận hành và hiệu suất nhân sự.','2026-10-15','2027-01-30','medium','active',2,'2026-10-12 14:00:00'),(5,'TTW-2026-005','Nâng cấp Hạ tầng Bảo mật & Chuẩn hóa ISO 27001','Rà soát lỗ hổng bảo mật ứng dụng, triển khai mã hóa dữ liệu đầu cuối (E2E), chuẩn hóa quy trình kiểm soát truy cập (RBAC) và lập hồ sơ đánh giá ISO 27001.','2026-11-20','2027-02-28','medium','planning',11,'2026-11-18 09:30:00');
/*!40000 ALTER TABLE `du_an` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `file_recipients`
--

DROP TABLE IF EXISTS `file_recipients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attachment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `encrypted_key` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_attachment_user` (`attachment_id`,`user_id`),
  KEY `idx_attachment_id` (`attachment_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_file_recipients_attachment` FOREIGN KEY (`attachment_id`) REFERENCES `tep_dinh_kem` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_file_recipients_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_recipients`
--

LOCK TABLES `file_recipients` WRITE;
/*!40000 ALTER TABLE `file_recipients` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_recipients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lich_su_trang_thai_cong_viec`
--

DROP TABLE IF EXISTS `lich_su_trang_thai_cong_viec`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lich_su_trang_thai_cong_viec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_status` varchar(50) NOT NULL,
  `new_status` varchar(50) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_task_logs_task` (`task_id`),
  KEY `fk_task_logs_user` (`user_id`),
  CONSTRAINT `fk_task_logs_task` FOREIGN KEY (`task_id`) REFERENCES `cong_viec` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_task_logs_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lich_su_trang_thai_cong_viec`
--

LOCK TABLES `lich_su_trang_thai_cong_viec` WRITE;
/*!40000 ALTER TABLE `lich_su_trang_thai_cong_viec` DISABLE KEYS */;
INSERT INTO `lich_su_trang_thai_cong_viec` VALUES (1,1,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-08-02 11:00:00'),(2,1,4,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-08-03 09:15:00'),(3,1,4,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-08-13 16:20:00'),(4,1,2,'submitted','approved','Tài liệu đặc tả rõ ràng, đầy đủ biểu đồ luồng và tiêu chí nghiệm thu.','2026-08-14 16:20:00'),(5,2,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-08-15 11:00:00'),(6,2,3,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-08-16 09:15:00'),(7,2,3,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-08-28 15:40:00'),(8,2,2,'submitted','approved','Giao diện trực quan, tối ưu thao tác cho nhân viên kinh doanh.','2026-08-29 15:40:00'),(9,3,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-08-31 11:00:00'),(10,3,5,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-09-01 09:15:00'),(11,3,5,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-09-18 16:50:00'),(12,3,2,'submitted','approved','API ổn định, phản hồi nhanh và có đầy đủ kiểm tra phân quyền.','2026-09-19 16:50:00'),(13,4,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-09-20 11:00:00'),(14,4,4,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-09-21 09:15:00'),(15,4,4,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-10-09 11:15:00'),(16,4,2,'submitted','approved','Hoàn thành đúng yêu cầu kỹ thuật, trễ 2 ngày do phía đối tác tổng đài đổi API.','2026-10-10 11:15:00'),(17,5,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-10-11 11:00:00'),(18,5,3,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-10-12 09:15:00'),(19,5,3,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-10-26 16:30:00'),(20,5,2,'submitted','approved','Toàn bộ 42 kịch bản UAT đều đạt, hệ thống đã go-live ổn định.','2026-10-27 16:30:00'),(21,6,11,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-09-15 11:00:00'),(22,6,10,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-09-16 09:15:00'),(23,6,10,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-09-30 16:10:00'),(24,6,11,'submitted','approved','Thiết kế chuẩn hóa tốt, đáp ứng bài toán đa kho và mở rộng sau này.','2026-10-01 16:10:00'),(25,7,11,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-10-04 11:00:00'),(26,7,9,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-10-05 09:15:00'),(27,7,9,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-10-26 17:00:00'),(28,7,11,'submitted','approved','Thanh toán sandbox và xử lý IPN callback hoạt động chính xác.','2026-10-27 17:00:00'),(29,8,11,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-10-31 11:00:00'),(30,8,7,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-01 09:15:00'),(31,8,7,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-11-23 16:30:00'),(32,9,11,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-11 11:00:00'),(33,9,7,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-12 09:15:00'),(34,10,11,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-01 11:00:00'),(35,10,4,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-02 09:15:00'),(36,10,4,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-11-23 16:30:00'),(37,10,11,'submitted','redo','Cần bổ sung xử lý trường hợp hủy hóa đơn khi khách trả hàng một phần và ghi log lỗi chi tiết hơn.','2026-11-23 11:00:00'),(38,11,11,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-24 11:00:00'),(39,13,6,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-10-01 11:00:00'),(40,13,5,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-10-02 09:15:00'),(41,13,5,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-10-18 15:00:00'),(42,13,6,'submitted','approved','Thiết kế hiện đại, tuân thủ tốt hướng dẫn Human Interface và Material Design.','2026-10-19 15:00:00'),(43,14,6,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-10-20 11:00:00'),(44,14,5,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-10-21 09:15:00'),(45,14,5,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-11-06 16:45:00'),(46,14,6,'submitted','approved','Hoạt động mượt trên cả iOS và Android, xử lý tốt giới hạn tần suất gửi OTP.','2026-11-07 16:45:00'),(47,15,6,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-04 11:00:00'),(48,15,4,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-05 09:15:00'),(49,15,4,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-11-23 16:30:00'),(50,16,6,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-13 11:00:00'),(51,16,7,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-14 09:15:00'),(52,17,6,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-09 11:00:00'),(53,17,7,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-10 09:15:00'),(54,17,7,'in_progress','blocked','Đang chờ phòng Hạ tầng mở cổng WebSocket trên môi trường staging.','2026-11-21 14:20:00'),(55,18,6,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-22 12:00:00'),(56,19,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-10-15 11:00:00'),(57,19,3,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-10-16 09:15:00'),(58,19,3,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-11-03 16:00:00'),(59,19,2,'submitted','approved','Pipeline chạy ổn định, thời gian xử lý giảm 40% so với bản thử nghiệm.','2026-11-04 16:00:00'),(60,20,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-10-31 11:00:00'),(61,20,5,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-01 09:15:00'),(62,20,5,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-11-18 10:30:00'),(63,20,2,'submitted','approved','Chỉ số chuẩn xác, đã đối chiếu khớp với báo cáo tài chính và PMO.','2026-11-19 10:30:00'),(64,21,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-14 11:00:00'),(65,21,7,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-15 09:15:00'),(66,22,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-07 11:00:00'),(67,22,3,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-08 09:15:00'),(68,22,3,'in_progress','submitted','Đã hoàn thiện sản phẩm và gửi Quản lý dự án nghiệm thu.','2026-11-23 16:30:00'),(69,23,2,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-22 12:00:00'),(70,24,11,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-20 11:00:00'),(71,24,4,'assigned','in_progress','Đã tiếp nhận và bắt đầu triển khai theo kế hoạch.','2026-11-21 09:15:00'),(72,25,11,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-22 12:00:00'),(73,26,11,'new','assigned','Phân công công việc cho nhân sự phụ trách.','2026-11-22 12:00:00');
/*!40000 ALTER TABLE `lich_su_trang_thai_cong_viec` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nhat_ky_truy_cap`
--

DROP TABLE IF EXISTS `nhat_ky_truy_cap`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhat_ky_truy_cap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_nhat_ky_truy_cap_user` (`user_id`),
  CONSTRAINT `fk_nhat_ky_truy_cap_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nhat_ky_truy_cap`
--

LOCK TABLES `nhat_ky_truy_cap` WRITE;
/*!40000 ALTER TABLE `nhat_ky_truy_cap` DISABLE KEYS */;
INSERT INTO `nhat_ky_truy_cap` VALUES (1,2,'create','project',1,'Khởi tạo dự án TTW-2026-001 - Hệ thống Quản trị Quan hệ Khách hàng (CRM 2.0)','2026-07-28 09:00:00'),(2,11,'create','project',2,'Khởi tạo dự án TTW-2026-002 - Nền tảng Thương mại Điện tử OmniChannel','2026-09-10 08:30:00'),(3,6,'create','project',3,'Khởi tạo dự án TTW-2026-003 - Ứng dụng Di động Chăm sóc Khách hàng (Mobile App)','2026-09-25 10:15:00'),(4,2,'create','project',4,'Khởi tạo dự án TTW-2026-004 - Hệ thống Phân tích Dữ liệu & Báo cáo BI Tự động','2026-10-12 14:00:00'),(5,11,'create','project',5,'Khởi tạo dự án TTW-2026-005 - Nâng cấp Hạ tầng Bảo mật & Chuẩn hóa ISO 27001','2026-11-18 09:30:00'),(6,8,'login','auth',NULL,'Đăng nhập hệ thống quản trị','2026-11-24 08:15:00'),(7,2,'login','auth',NULL,'Đăng nhập hệ thống quản lý dự án','2026-11-24 08:25:00'),(8,11,'assign','task',11,'Phân công kiểm thử tải cho dự án OmniChannel','2026-11-24 09:10:00'),(9,6,'review','task',14,'Duyệt hoàn thành hạng mục đăng nhập OTP Mobile App','2026-11-23 15:40:00'),(10,2,'review','task',20,'Đánh giá bộ chỉ số KPI điều hành dự án BI Tự động','2026-11-23 16:05:00'),(11,11,'create','project',5,'Khởi tạo dự án Nâng cấp Hạ tầng Bảo mật & ISO 27001','2026-11-18 09:30:00');
/*!40000 ALTER TABLE `nhat_ky_truy_cap` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tai_khoan`
--

DROP TABLE IF EXISTS `tai_khoan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tai_khoan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `public_key_jwk` longtext DEFAULT NULL,
  `role` enum('admin','manager','employee') NOT NULL DEFAULT 'employee',
  `department` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tai_khoan`
--

LOCK TABLES `tai_khoan` WRITE;
/*!40000 ALTER TABLE `tai_khoan` DISABLE KEYS */;
INSERT INTO `tai_khoan` VALUES (1,'Nguyễn Hoàng Nam','admin@taskflow.local','$2y$12$ZTAdewvUJLPqmSxU6o3lROMlEjs7mfWX.EAySFNB3VBI9goD2npf2','{\"alg\":\"RSA-OAEP-256\",\"e\":\"AQAB\",\"ext\":true,\"key_ops\":[\"encrypt\"],\"kty\":\"RSA\",\"n\":\"3_ww1nPtJKTnGmCX94cBn5oakUNFf2WYuO5IKlQj6K7f7p-sUWmK2ggXWxKXoo2PntGamJDvhaZ555JZiqJ2i5fg6WJCN879a6G2tKxx1ejveyu1R1YtP6voQu5qhxC2NlrExJU8Ne_A_EqXZYEQinF47bwKb1zMjbwZMc125p9swGjVS6N8VCBvrx9uwVHDmmLrt2lOdmygyZ1jW_9jrBtZ5ZpmFgnHCk9qNfUElWOExvhc-YS0xZeCy7eBlRCEtfdyi-niD_b7svKp_KQxQobGPnp-a0O1v7qUyRFsd3K0RVHKZhHyLUtNeqoswb00VrbBgRA12yuFECXMXRlcwQ\"}','admin','Ban Công nghệ Thông tin','active','2026-04-02 14:49:05'),(2,'Trần Minh Tuấn','manager@taskflow.local','$2y$12$xxANoFxi4jMPl1ANwVf5WeuJ.fq3GTynS46wxUuyj7B1uCAEMBA86',NULL,'manager','Văn phòng Quản lý Dự án (PMO)','active','2026-04-02 14:49:05'),(3,'Lê Hữu Nghĩa','employee1@taskflow.local','$2y$12$4b1dzmsaU4obe86xP/Uybu8R0xaXghTHdD/gRNB4WWFf9b48mCVJe',NULL,'employee','Phòng Kỹ thuật Backend','active','2026-04-02 14:49:05'),(4,'Phạm Thảo Vy','employee2@taskflow.local','$2y$12$vKjlixWa1n9xltdBeB1zYeQLhE01VNGJ2Tul.pDTP32OLdIzGePf6',NULL,'employee','Phòng Thiết kế UI/UX','active','2026-04-02 14:49:05'),(5,'Nguyễn Văn An','nva@gmail.com','$2y$10$N5/kqv21.8pRtq5NeglN3.fE8uIYbQh.byr7QR1CdMpWUqMFigFEe','{\"alg\":\"RSA-OAEP-256\",\"e\":\"AQAB\",\"ext\":true,\"key_ops\":[\"encrypt\"],\"kty\":\"RSA\",\"n\":\"w9SQdHng1zrBCnk-jeJfzgX7v50Vr-lYC-KTSJpYTvR9xKumtYGNYFlYKpGPQfe4pvsT6syQrKA0t_bFuxuKhEaZM1WRwqCq527JEtwar-152yCOQGXWHy99_7yXNUuaIP2e6Dox8UtMEfZC9dRNE85Ew7aLv_S4kYomEO4Cji0LtrGuv4NtIGmr6N2NkzYFQ9cb7CZivs4vWIAVT3PQpKv09GnvivTVVTk1h7fr2KyKO1RzVNP0W91kT9RMMpWOVyBPzyUnIg9jQ0Tl_PLLP0HIaV6wb0b09JGbsvmk5SNRe69WYmuZLBToezMRQo5b46slG5uawGMjbyJDQupm1w\"}','employee','Phòng Phát triển Frontend','active','2026-04-02 18:06:15'),(6,'Hoàng Minh Quân','abc@gmail.com','$2y$10$LfbspEBFWs60P0xB9v9Q5O21M0x.Ld5p3gQo8a.PjQbjSmLpMShjq',NULL,'manager','Khối Sản phẩm Số','active','2026-04-02 18:20:28'),(7,'Trần Quốc Bảo','nv1@gmail.com','$2y$10$SuGN/R6g0W8qbUDS56Al1uXHdGTw.hAq5gynkT282MXeS8v6TmGRO','{\"alg\":\"RSA-OAEP-256\",\"e\":\"AQAB\",\"ext\":true,\"key_ops\":[\"encrypt\"],\"kty\":\"RSA\",\"n\":\"qpSV_-_wUogG42FisgtRxy1vRjbELMDn0isVFRbPL8jw6UyUcnmKN_l4BbQ3dOJsgQB_EotxT8duqMVkNvm3U4_mLCb2-pUBX7zGKaRSwMJqlMO-HWfjgg-ElwCaBwWGqI2lns_f7WtSIUVT4aQu-jT-3CsWP4gsjT2dmrTFJpwgY66Q0Z3hX9C0TSmMuVphxSfAf2S62iUMWLUOhCkH-1300fkFM0Q3jO2-KD2mSAzCbm-pbG0BmnY0LtWTtAYyCQFSF1iFvyt5Dcp37JZSWBeT_fBvAG-oV4fJvo3Bm6xUIanvSyOZ9MpZFu-M7pacLN0mZrFgTr929hsuqiCh0w\"}','employee','Phòng Kỹ thuật Hệ thống','active','2026-04-03 01:28:33'),(8,'TRAN TRUNG','admin@gmail.com','$2y$10$FmDvnXlVZZK3NBS7.qPUt.nGKoEYJ64p6HemgRX9HRw2o8h1PB4fq','{\"alg\":\"RSA-OAEP-256\",\"e\":\"AQAB\",\"ext\":true,\"key_ops\":[\"encrypt\"],\"kty\":\"RSA\",\"n\":\"paH3wqnRIUfQGShSJXYjLso7cx3mEyFUyxXhAZ5BHd8ELKlHQzsVkjQy8JLSBKwOHkxUyFBlgDc1t1T-i-VMs7xq9PxIsYeGcfyE8Af0mTc_3L1mxJaJ0-Z4GVKUJlBuIBdyGV9uZAN2kQf4ghiVabNkicO8mbmgFVJRQCcHWn6pOWDQuEQ6zkkv1EHoIR4_dvHtMq00qVVKOqQI_UGMoVQTCR7xpLbAQpTAV12F_1XL9vPhP5ppkgxqQ1lRiltMcShyMgwXEvG0xTLjKANcCaFALXq84fkFrYeTPHwEFUSLsihEaYwOybfLaa6VW6gts71L7__czJxDEdg652AY3w\"}','admin','Ban Giám đốc','active','2026-05-23 02:38:27'),(9,'Nguyễn Thị Minh Anh','nv2@gmail.com','$2y$10$pdeljC.CMfCI/lysFjBaSuA2iegv.CSDqfXf58ESbCeI8GpfeWc82','{\"alg\":\"RSA-OAEP-256\",\"e\":\"AQAB\",\"ext\":true,\"key_ops\":[\"encrypt\"],\"kty\":\"RSA\",\"n\":\"t4RqOgUYfJ_S_ZG6SiTxJcva9_jcYFw3MgZmis5tscCKp3mjWBB_MW2pzQdOycHFXqcgy7mz0SUgiRiWy2U5d3mtrX8Dn-BpeLq61sRxwdsHiNd9ZIsyepBvot6azWAsHgC1HdCE0kKBnc2mbvsJN8blQr9LjBC7c0fguuShkpx7SAWYHGIrdLCSm0XYM6DVjWWQJQbi0JUNS6avShjIFw4u_9wVcykWTbDKILygSBQHtZu2PNQS-zsZJ1EMDDMlAeOxBZb0Yjqn71YKs1724Bq8Z4LWivikOHFsjjQML5HGWCkMh5dc4rVS3QkY-cqtr73ddjX0rpBljinIuhanMw\"}','employee','Phòng Kiểm thử & QA','active','2026-05-23 02:39:28'),(10,'Đỗ Đức Cường','nv3@gmail.com','$2y$10$s/crPevfqUXBxZ4FCYFt4ujIG.uoeihIYGZLBBXgnKKVHiM0wEYmq',NULL,'employee','Phòng Dữ liệu & BI','active','2026-05-23 02:39:44'),(11,'Trần Thanh Trúc','tp1@gmail.com','$2y$10$Z2gwvgKQyQso0BfqOJqFauS1fxyo0Ux6AWO3a7gcO/FyG3O4Mcqf2','{\"alg\":\"RSA-OAEP-256\",\"e\":\"AQAB\",\"ext\":true,\"key_ops\":[\"encrypt\"],\"kty\":\"RSA\",\"n\":\"xmULJkBGaj-wDTlwJloBJLm9PF_MG_K3OS7djllnAUg5GUQWJ4Dl6OM2frwnkRi07wQEXv0lR5Gl-GqxXbauMYLjETzEARooBmMXCw4uUrqUyoK7mY3Q8rCiBYZmWQ6L9LoSzIE9GJPsTSQpzuoD3F65JZi7PuABc25mEzrquiqppfydtHZtif4V3GUIl7mZx6jPwtKQNQAlvh10rkr1Ux2AD5WVLbu5JSKsRPS4SlSDo5jbzqVxxnEPxFnmiSA1-r9M6E5PHfyL8sqUtzpaeswDwxI34aKyJUa8qwgwj2HXLMp09r60gGCOk9NT2CBXvpxuLLrqC94_3kvLtFktFw\"}','manager','Phòng Vận hành & Chuỗi cung ứng','active','2026-05-23 02:40:16');
/*!40000 ALTER TABLE `tai_khoan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tep_dinh_kem`
--

DROP TABLE IF EXISTS `tep_dinh_kem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tep_dinh_kem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `module_name` varchar(50) NOT NULL DEFAULT 'cong_viec',
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `mime_type` varchar(150) DEFAULT NULL,
  `file_size` int(11) NOT NULL,
  `encrypted_path` varchar(255) DEFAULT NULL,
  `encrypted_blob` longblob DEFAULT NULL,
  `storage_mode` varchar(20) NOT NULL DEFAULT 'db',
  `uploaded_by` int(11) NOT NULL,
  `file_type` varchar(20) DEFAULT 'employee',
  `upload_reason` varchar(255) DEFAULT NULL,
  `encrypted_key` longtext DEFAULT NULL COMMENT 'Khóa AES được bọc bằng RSA (base64)',
  `iv` varchar(255) DEFAULT NULL COMMENT 'IV cho AES-256-CBC (base64)',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tep_dinh_kem_task` (`task_id`),
  KEY `fk_tep_dinh_kem_user` (`uploaded_by`),
  KEY `idx_tep_dinh_kem_project` (`project_id`),
  CONSTRAINT `fk_tep_dinh_kem_task` FOREIGN KEY (`task_id`) REFERENCES `cong_viec` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tep_dinh_kem_user` FOREIGN KEY (`uploaded_by`) REFERENCES `tai_khoan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tep_dinh_kem`
--

LOCK TABLES `tep_dinh_kem` WRITE;
/*!40000 ALTER TABLE `tep_dinh_kem` DISABLE KEYS */;
/*!40000 ALTER TABLE `tep_dinh_kem` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thanh_vien_du_an`
--

DROP TABLE IF EXISTS `thanh_vien_du_an`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thanh_vien_du_an` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_role` enum('lead','member','reviewer') NOT NULL DEFAULT 'member',
  `joined_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_project_member` (`project_id`,`user_id`),
  KEY `fk_thanh_vien_du_an_user` (`user_id`),
  CONSTRAINT `fk_thanh_vien_du_an_project` FOREIGN KEY (`project_id`) REFERENCES `du_an` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_thanh_vien_du_an_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thanh_vien_du_an`
--

LOCK TABLES `thanh_vien_du_an` WRITE;
/*!40000 ALTER TABLE `thanh_vien_du_an` DISABLE KEYS */;
INSERT INTO `thanh_vien_du_an` VALUES (1,1,2,'lead','2026-07-28 09:00:00'),(2,1,11,'reviewer','2026-07-28 09:00:00'),(3,1,3,'member','2026-07-28 11:00:00'),(4,1,4,'member','2026-07-28 11:00:00'),(5,1,5,'member','2026-07-28 11:00:00'),(6,1,7,'member','2026-07-28 11:00:00'),(7,1,9,'member','2026-07-28 11:00:00'),(8,2,11,'lead','2026-09-10 08:30:00'),(9,2,2,'reviewer','2026-09-10 08:30:00'),(10,2,3,'member','2026-09-10 10:30:00'),(11,2,4,'member','2026-09-10 10:30:00'),(12,2,5,'member','2026-09-10 10:30:00'),(13,2,7,'member','2026-09-10 10:30:00'),(14,2,9,'member','2026-09-10 10:30:00'),(15,2,10,'member','2026-09-10 10:30:00'),(16,3,6,'lead','2026-09-25 10:15:00'),(17,3,2,'reviewer','2026-09-25 10:15:00'),(18,3,4,'member','2026-09-25 12:15:00'),(19,3,5,'member','2026-09-25 12:15:00'),(20,3,7,'member','2026-09-25 12:15:00'),(21,3,9,'member','2026-09-25 12:15:00'),(22,3,10,'member','2026-09-25 12:15:00'),(23,4,2,'lead','2026-10-12 14:00:00'),(24,4,6,'reviewer','2026-10-12 14:00:00'),(25,4,3,'member','2026-10-12 16:00:00'),(26,4,5,'member','2026-10-12 16:00:00'),(27,4,7,'member','2026-10-12 16:00:00'),(28,4,9,'member','2026-10-12 16:00:00'),(29,4,10,'member','2026-10-12 16:00:00'),(30,5,11,'lead','2026-11-18 09:30:00'),(31,5,6,'reviewer','2026-11-18 09:30:00'),(32,5,3,'member','2026-11-18 11:30:00'),(33,5,4,'member','2026-11-18 11:30:00'),(34,5,7,'member','2026-11-18 11:30:00'),(35,5,9,'member','2026-11-18 11:30:00');
/*!40000 ALTER TABLE `thanh_vien_du_an` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thong_bao`
--

DROP TABLE IF EXISTS `thong_bao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thong_bao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'manual',
  `scope` varchar(30) NOT NULL DEFAULT 'project',
  `project_id` int(11) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tb_project` (`project_id`),
  KEY `idx_tb_task` (`task_id`),
  KEY `idx_tb_created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thong_bao`
--

LOCK TABLES `thong_bao` WRITE;
/*!40000 ALTER TABLE `thong_bao` DISABLE KEYS */;
INSERT INTO `thong_bao` VALUES (1,'Chúc mừng hoàn thành dự án CRM 2.0 (TTW-2026-001)','Dự án Hệ thống Quản trị Quan hệ Khách hàng (CRM 2.0) đã nghiệm thu thành công toàn bộ hạng mục và chính thức đưa vào vận hành. Cảm ơn sự đóng góp của toàn đội dự án.','manual','project',1,NULL,'?route=du_an/show&id=1',2,'2026-10-31 16:00:00','2026-10-31 16:00:00'),(2,'Khởi động kế hoạch chuẩn hóa bảo mật ISO 27001','Dự án Nâng cấp Hạ tầng Bảo mật & Chuẩn hóa ISO 27001 (TTW-2026-005) đã được khởi tạo. Các thành viên liên quan vui lòng xem phân công công việc giai đoạn 1.','manual','project',5,NULL,'?route=du_an/show&id=5',11,'2026-11-20 09:00:00','2026-11-20 09:00:00'),(3,'Nhắc nhở rà soát tiến độ tuần 4 tháng 11/2026','Các nhóm dự án OmniChannel, Mobile App và BI Tự động vui lòng cập nhật trạng thái công việc trước 17:00 thứ Sáu để tổng hợp báo cáo KPI tháng 11.','manual','system',NULL,NULL,'?route=kpi/index',8,'2026-11-24 08:30:00','2026-11-24 08:30:00'),(4,'Công việc chờ duyệt: Xây dựng bộ kết nối đồng bộ đơn hàng Shopee & Lazada','Nhân sự phụ trách đã nộp kết quả công việc \"Xây dựng bộ kết nối đồng bộ đơn hàng Shopee & Lazada\" thuộc dự án \"Nền tảng Thương mại Điện tử OmniChannel\". Vui lòng kiểm tra và đánh giá.','task_submitted','project',2,8,'?route=cong_viec/show&id=8',7,'2026-11-23 16:35:00','2026-11-23 16:35:00'),(5,'Yêu cầu chỉnh sửa: Xây dựng module xuất hóa đơn điện tử tự động','Quản lý dự án đã phản hồi và yêu cầu cập nhật lại công việc \"Xây dựng module xuất hóa đơn điện tử tự động\". Vui lòng xem chi tiết nhận xét.','task_reviewed','project',2,10,'?route=cong_viec/show&id=10',11,'2026-11-23 11:05:00','2026-11-23 11:05:00'),(6,'Công việc chờ duyệt: Xây dựng hệ thống thông báo đẩy (Push Notification) theo phân khúc','Nhân sự phụ trách đã nộp kết quả công việc \"Xây dựng hệ thống thông báo đẩy (Push Notification) theo phân khúc\" thuộc dự án \"Ứng dụng Di động Chăm sóc Khách hàng (Mobile App)\". Vui lòng kiểm tra và đánh giá.','task_submitted','project',3,15,'?route=cong_viec/show&id=15',4,'2026-11-23 16:35:00','2026-11-23 16:35:00'),(7,'Công việc chờ duyệt: Thiết lập cơ chế xuất báo cáo PDF/Excel định kỳ gửi Ban Giám đốc','Nhân sự phụ trách đã nộp kết quả công việc \"Thiết lập cơ chế xuất báo cáo PDF/Excel định kỳ gửi Ban Giám đốc\" thuộc dự án \"Hệ thống Phân tích Dữ liệu & Báo cáo BI Tự động\". Vui lòng kiểm tra và đánh giá.','task_submitted','project',4,22,'?route=cong_viec/show&id=22',3,'2026-11-23 16:35:00','2026-11-23 16:35:00');
/*!40000 ALTER TABLE `thong_bao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `thong_bao_nguoi_nhan`
--

DROP TABLE IF EXISTS `thong_bao_nguoi_nhan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `thong_bao_nguoi_nhan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_notif_user` (`notification_id`,`user_id`),
  KEY `idx_tbnn_user` (`user_id`),
  KEY `idx_tbnn_notif` (`notification_id`),
  CONSTRAINT `fk_tbnn_notif` FOREIGN KEY (`notification_id`) REFERENCES `thong_bao` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tbnn_user` FOREIGN KEY (`user_id`) REFERENCES `tai_khoan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `thong_bao_nguoi_nhan`
--

LOCK TABLES `thong_bao_nguoi_nhan` WRITE;
/*!40000 ALTER TABLE `thong_bao_nguoi_nhan` DISABLE KEYS */;
INSERT INTO `thong_bao_nguoi_nhan` VALUES (1,1,1,1,'2026-10-31 16:30:00','2026-10-31 16:00:00'),(2,1,2,1,'2026-10-31 16:30:00','2026-10-31 16:00:00'),(3,1,3,1,'2026-10-31 16:30:00','2026-10-31 16:00:00'),(4,1,4,1,'2026-10-31 16:30:00','2026-10-31 16:00:00'),(5,1,5,1,'2026-10-31 16:30:00','2026-10-31 16:00:00'),(6,1,7,1,'2026-10-31 16:30:00','2026-10-31 16:00:00'),(7,1,8,1,'2026-10-31 16:30:00','2026-10-31 16:00:00'),(8,1,9,1,'2026-10-31 16:30:00','2026-10-31 16:00:00'),(9,1,11,1,'2026-10-31 16:30:00','2026-10-31 16:00:00'),(10,2,1,0,NULL,'2026-11-20 09:00:00'),(11,2,3,0,NULL,'2026-11-20 09:00:00'),(12,2,4,0,NULL,'2026-11-20 09:00:00'),(13,2,6,0,NULL,'2026-11-20 09:00:00'),(14,2,7,0,NULL,'2026-11-20 09:00:00'),(15,2,8,0,NULL,'2026-11-20 09:00:00'),(16,2,9,0,NULL,'2026-11-20 09:00:00'),(17,2,11,0,NULL,'2026-11-20 09:00:00'),(18,3,1,0,NULL,'2026-11-24 08:30:00'),(19,3,2,0,NULL,'2026-11-24 08:30:00'),(20,3,3,0,NULL,'2026-11-24 08:30:00'),(21,3,4,0,NULL,'2026-11-24 08:30:00'),(22,3,5,0,NULL,'2026-11-24 08:30:00'),(23,3,6,0,NULL,'2026-11-24 08:30:00'),(24,3,7,0,NULL,'2026-11-24 08:30:00'),(25,3,8,0,NULL,'2026-11-24 08:30:00'),(26,3,9,0,NULL,'2026-11-24 08:30:00'),(27,3,10,0,NULL,'2026-11-24 08:30:00'),(28,3,11,0,NULL,'2026-11-24 08:30:00'),(29,4,11,0,NULL,'2026-11-23 16:35:00'),(30,4,1,0,NULL,'2026-11-23 16:35:00'),(31,4,8,0,NULL,'2026-11-23 16:35:00'),(32,5,4,0,NULL,'2026-11-23 11:05:00'),(33,6,6,0,NULL,'2026-11-23 16:35:00'),(34,6,1,0,NULL,'2026-11-23 16:35:00'),(35,6,8,0,NULL,'2026-11-23 16:35:00'),(36,7,2,0,NULL,'2026-11-23 16:35:00'),(37,7,1,0,NULL,'2026-11-23 16:35:00'),(38,7,8,0,NULL,'2026-11-23 16:35:00');
/*!40000 ALTER TABLE `thong_bao_nguoi_nhan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'data'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-24 19:06:28
