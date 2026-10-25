<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Lớp xử lý xác thực đăng nhập và kiểm tra quyền truy cập.
 */
final class Auth
{
    /**
     * Cấu hình chống Brute Force (DVWA Medium + Session Lockout):
     * - FAIL_DELAY: Độ trễ 2 giây mỗi khi đăng nhập sai (chuẩn DVWA Medium).
     * - MAX_ATTEMPTS: Số lần thử sai tối đa liên tiếp trước khi khóa tạm thời.
     * - LOCKOUT_TIME: Thời gian khóa tạm thời (giây) khi vượt quá số lần thử.
     */
    public const FAIL_DELAY = 2;
    public const MAX_ATTEMPTS = 5;
    public const LOCKOUT_TIME = 120;

    /**
     * Lấy thông tin người dùng đang đăng nhập từ session.
     */
    public static function user(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return (new User())->find((int) $_SESSION['user_id']);
    }

    /**
     * Kiểm tra phiên hiện tại có đang bị khóa tạm thời do Brute Force hay không.
     */
    public static function isLockedOut(): bool
    {
        $lockedUntil = (int) ($_SESSION['login_locked_until'] ?? 0);
        if ($lockedUntil <= 0) {
            return false;
        }

        if (time() < $lockedUntil) {
            return true;
        }

        // Hết thời gian khóa thì tự động mở khóa và reset bộ đếm
        self::clearFailedAttempts();
        return false;
    }

    /**
     * Lấy số giây còn lại của thời gian khóa đăng nhập tạm thời.
     */
    public static function remainingLockoutSeconds(): int
    {
        $lockedUntil = (int) ($_SESSION['login_locked_until'] ?? 0);
        return max(0, $lockedUntil - time());
    }

    /**
     * Lấy số lần đăng nhập thất bại liên tiếp trong phiên hiện tại.
     */
    public static function failedAttempts(): int
    {
        return (int) ($_SESSION['login_attempts'] ?? 0);
    }

    /**
     * Ghi nhận 1 lần đăng nhập thất bại, kích hoạt khóa nếu chạm ngưỡng và sleep(2) theo DVWA Medium.
     */
    public static function recordFailedAttempt(): int
    {
        $attempts = self::failedAttempts() + 1;
        $_SESSION['login_attempts'] = $attempts;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $_SESSION['login_locked_until'] = time() + self::LOCKOUT_TIME;
        }

        // Cơ chế DVWA Medium: làm chậm 2 giây khi đăng nhập thất bại để chống Brute Force tốc độ cao
        sleep(self::FAIL_DELAY);

        return $attempts;
    }

    /**
     * Xóa bộ đếm số lần đăng nhập sai sau khi đăng nhập thành công hoặc hết hạn khóa.
     */
    public static function clearFailedAttempts(): void
    {
        unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
    }

    /**
     * Thử đăng nhập bằng email và mật khẩu (có tích hợp chống Brute Force).
     */
    public static function attempt(string $email, string $password): bool
    {
        if (self::isLockedOut()) {
            sleep(self::FAIL_DELAY);
            return false;
        }

        $model = new User();
        $user = $model->findByEmail($email);

        if (!$user || $user['status'] !== 'active') {
            self::recordFailedAttempt();
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            self::recordFailedAttempt();
            return false;
        }

        self::clearFailedAttempts();
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }

    /**
     * Xóa thông tin đăng nhập khỏi session.
     */
    public static function logout(): void
    {
        unset($_SESSION['user_id'], $_SESSION['user_role']);
    }

    /**
     * Bắt buộc người dùng phải đăng nhập trước khi truy cập module.
     */
    public static function requireLogin(): void
    {
        if (!self::user()) {
            \flash('danger', 'Vui lòng đăng nhập để tiếp tục.');
            \redirect('auth/login');
        }
    }

    /**
     * Bắt buộc người dùng phải có một trong các vai trò cho phép.
     */
    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        $user = self::user();

        if (!$user || !in_array($user['role'], $roles, true)) {
            http_response_code(403);
            exit('Bạn không có quyền truy cập chức năng này.');
        }
    }
}
