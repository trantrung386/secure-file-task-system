<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Lớp phòng chống CSRF ở mức Medium (theo mô hình DVWA Medium):
 * Kiểm tra HTTP Referer / Origin của request có khớp với tên miền của hệ thống (APP_URL / SERVER_NAME) hay không.
 */
final class Csrf
{
    /**
     * Kiểm tra nguồn gốc request (Referer / Origin) có chứa tên miền hợp lệ của máy chủ không.
     */
    public static function verifyOrigin(): bool
    {
        $configuredHost = defined('APP_URL') && APP_URL !== ''
            ? strtolower((string) parse_url(APP_URL, PHP_URL_HOST))
            : '';
        $runtimeHost = strtolower((string) parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''), PHP_URL_HOST));

        // Khi chạy local (localhost / 127.0.0.1) hoặc khi đã cấu hình APP_URL theo tên miền thật thì lấy theo APP_URL.
        // Nếu deploy lên tên miền thật mà quên sửa APP_URL (vẫn để localhost), tự động lấy theo tên miền thật ($runtimeHost).
        if ($configuredHost !== '' && ($configuredHost !== 'localhost' || in_array($runtimeHost, ['localhost', '127.0.0.1', '::1'], true))) {
            $serverName = $configuredHost;
        } else {
            $serverName = $runtimeHost !== '' ? $runtimeHost : $configuredHost;
        }

        if ($serverName === '') {
            return false;
        }

        // Lấy thông tin nguồn gửi request từ HTTP_REFERER hoặc HTTP_ORIGIN
        $referer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
        $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));

        $source = $referer !== '' ? $referer : $origin;
        if ($source === '' || strtolower($source) === 'null') {
            return false;
        }

        // Cơ chế DVWA Medium: kiểm tra tên miền của máy chủ có nằm trong Referer/Origin hay không
        return stripos($source, $serverName) !== false;
    }

    /**
     * Bắt buộc request phải có Referer / Origin hợp lệ từ cùng tên miền hệ thống, nếu không sẽ chặn (403 Forbidden).
     */
    public static function requireValid(): void
    {
        if (!self::verifyOrigin()) {
            http_response_code(403);
            exit('Yêu cầu bị từ chối: Phát hiện dấu hiệu tấn công CSRF (Referer/Origin không hợp lệ).');
        }
    }
}
