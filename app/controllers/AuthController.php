<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Log;

/**
 * Controller xử lý đăng nhập và đăng xuất hệ thống.
 */
class AuthController extends BaseController
{
    /**
     * Hiển thị form đăng nhập và xử lý xác thực người dùng.
     */
    public function login(): void
    {
        if (Auth::user()) {
            \redirect('dashboard/index');
        }

        if (\is_post()) {
            $email = trim($_POST['email'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            if (Auth::attempt($email, $password)) {
                $user = Auth::user();
                (new Log())->create($user['id'], 'login', 'auth', null, 'Đăng nhập hệ thống');
                \flash('success', 'Đăng nhập thành công.');
                \redirect('dashboard/index');
            }

            \flash('danger', 'Email hoặc mật khẩu không đúng, hoặc tài khoản đang bị khóa.');
        }

        $this->render('auth/login', [], 'Đăng nhập');
    }

    /**
     * Đăng xuất người dùng hiện tại và ghi log thao tác.
     */
    public function logout(): void
    {
        $user = Auth::user();
        if ($user) {
            (new Log())->create($user['id'], 'logout', 'auth', null, 'Đăng xuất hệ thống');
        }

        Auth::logout();
        \flash('success', 'Bạn đã đăng xuất.');
        \redirect('auth/login');
    }
}
