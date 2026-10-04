<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Lớp nạp và quản lý biến môi trường từ tệp .env
 * Giúp tách biệt thông tin nhạy cảm (DB, Secret Key) khỏi mã nguồn.
 */
final class Env
{
    /**
     * Nạp các biến từ tệp .env vào môi trường nếu tệp tồn tại.
     */
    public static function load(string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Bỏ qua dòng trống hoặc dòng chú thích bắt đầu bằng #
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Loại bỏ dấu nháy kép hoặc nháy đơn bao quanh giá trị
            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            // Gán vào môi trường nếu chưa có
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Lấy giá trị biến môi trường có hỗ trợ giá trị mặc định.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $val = getenv($key);
        if ($val === false) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }

        if (is_string($val)) {
            return match (strtolower($val)) {
                'true', '(true)' => true,
                'false', '(false)' => false,
                'empty', '(empty)' => '',
                'null', '(null)' => null,
                default => $val,
            };
        }

        return $val;
    }
}
