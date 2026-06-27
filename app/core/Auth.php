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
     * Thử đăng nhập bằng email và mật khẩu.
     */
    public static function attempt(string $email, string $password): bool
    {
        $model = new User();
        $user = $model->findByEmail($email);

        if (!$user || $user['status'] !== 'active') {
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            return false;
        }

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
