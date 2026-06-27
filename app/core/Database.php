<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

/**
 * Quản lý kết nối PDO theo kiểu singleton để toàn hệ thống dùng chung một kết nối.
 */
final class Database
{
    private static ?PDO $instance = null;

    /**
     * Mở kết nối MySQL và ép charset utf8 cho XAMPP/MySQL cũ.
     */
    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8';

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8 COLLATE utf8_general_ci',
                ]);
            } catch (PDOException $exception) {
                http_response_code(500);
                exit('Không thể kết nối MySQL. Kiểm tra lại file app/config/config.php và import CSDL.');
            }
        }

        return self::$instance;
    }
}
