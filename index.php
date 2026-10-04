<?php
declare(strict_types=1);

/**
 * Root Forwarder: Chuyển tiếp request an toàn tới Public Front Controller.
 * Đảm bảo ứng dụng chạy trơn tru trên cả môi trường XAMPP htdocs mặc định và VirtualHost.
 */

require __DIR__ . '/public/index.php';
