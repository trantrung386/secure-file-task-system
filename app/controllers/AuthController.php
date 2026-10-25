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

        $error = null;

        if (\is_post()) {
            $this->requirePostCsrf();

            // Kiểm tra nếu phiên đang bị khóa tạm thời do nhập sai quá số lần cho phép
            if (Auth::isLockedOut()) {
                sleep(Auth::FAIL_DELAY);
                $remain = Auth::remainingLockoutSeconds();
                $error = "Tạm khóa đăng nhập quá " . Auth::MAX_ATTEMPTS . " lần sai. Thử lại sau {$remain}s.";
                $this->render('auth/login', ['error' => $error], 'Đăng nhập');
                return;
            }

            $validator = \App\Core\Validator::make($_POST, [
                'email' => 'required|email|max:150',
                'password' => 'required|string|min:1|max:255',
            ]);

            if ($validator->fails()) {
                $error = $validator->firstError();
                $this->render('auth/login', ['error' => $error], 'Đăng nhập');
                return;
            }

            $validated = $validator->validated();
            $email = $validated['email'];
            $password = (string) ($_POST['password'] ?? '');

            if (Auth::attempt($email, $password)) {
                $user = Auth::user();
                (new Log())->create($user['id'], 'login', 'auth', null, 'Đăng nhập hệ thống');
                \flash('success', 'Đăng nhập thành công.');
                \redirect('dashboard/index');
            }

            // Ghi nhật ký cảnh báo đăng nhập thất bại / dấu hiệu Brute Force
            $attempts = Auth::failedAttempts();
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            (new Log())->create(
                null,
                'login_failed',
                'auth',
                null,
                "Đăng nhập thất bại lần {$attempts} với email: {$email} (IP: {$ip})"
            );

            if (Auth::isLockedOut()) {
                $remain = Auth::remainingLockoutSeconds();
                $error = "Nhập sai quá " . Auth::MAX_ATTEMPTS . " lần. Tạm khóa trong {$remain}s.";
            } else {
                $attemptsLeft = max(0, Auth::MAX_ATTEMPTS - $attempts);
                $error = "Sai email hoặc mật khẩu (còn {$attemptsLeft} lần thử).";
            }
        }

        $this->render('auth/login', ['error' => $error], 'Đăng nhập');
    }

    /**
     * Đăng xuất người dùng hiện tại và ghi log thao tác.
     */
    public function logout(): void
    {
        $this->requirePostCsrf();

        $user = Auth::user();
        if ($user) {
            (new Log())->create($user['id'], 'logout', 'auth', null, 'Đăng xuất hệ thống');
        }

        Auth::logout();
        \flash('success', 'Bạn đã đăng xuất.');
        \redirect('auth/login');
    }
}
