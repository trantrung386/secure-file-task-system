<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\BaseController;
use App\Models\Log;
use App\Models\User;

/**
 * Controller cho module quản lý tài khoản của Admin.
 */
class UserController extends BaseController
{
    /**
     * Hiển thị danh sách tài khoản.
     */
    public function index(): void
    {
        $this->requireRole(['admin']);
        $tai_khoan = (new User())->all();
        $this->render('tai_khoan/index', ['tai_khoan' => $tai_khoan], 'Quản lý tài khoản');
    }

    /**
     * Mở form tạo tài khoản mới.
     */
    public function create(): void
    {
        $this->requireRole(['admin']);
        $this->render('tai_khoan/form', ['user' => null], 'Tạo tài khoản');
    }

    /**
     * Lưu tài khoản mới vào CSDL.
     */
    public function store(): void
    {
        $this->requireRole(['admin']);
        if (!\is_post()) {
            \redirect('tai_khoan/index');
        }

        $model = new User();
        $email = trim($_POST['email'] ?? '');

        if ($model->findByEmail($email)) {
            \flash('danger', 'Email đã tồn tại.');
            \redirect('tai_khoan/create');
        }

        $ok = $model->create([
            'name' => trim($_POST['name'] ?? ''),
            'email' => $email,
            'password' => (string) ($_POST['password'] ?? ''),
            'role' => $_POST['role'] ?? 'employee',
            'department' => trim($_POST['department'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
        ]);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'create', 'user', null, 'Tạo tài khoản mới: ' . $email);
            \flash('success', 'Tạo tài khoản thành công.');
        } else {
            \flash('danger', 'Tạo tài khoản thất bại.');
        }

        \redirect('tai_khoan/index');
    }

    /**
     * Mở form cập nhật tài khoản.
     */
    public function edit(int $id): void
    {
        $this->requireRole(['admin']);
        $user = (new User())->find($id);
        $this->render('tai_khoan/form', ['user' => $user], 'Cập nhật tài khoản');
    }

    /**
     * Cập nhật tài khoản đã có.
     */
    public function update(int $id): void
    {
        $this->requireRole(['admin']);
        if (!\is_post()) {
            \redirect('tai_khoan/index');
        }

        $ok = (new User())->update($id, [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => (string) ($_POST['password'] ?? ''),
            'role' => $_POST['role'] ?? 'employee',
            'department' => trim($_POST['department'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
        ]);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'update', 'user', $id, 'Cập nhật tài khoản');
            \flash('success', 'Cập nhật tài khoản thành công.');
        } else {
            \flash('danger', 'Cập nhật tài khoản thất bại.');
        }

        \redirect('tai_khoan/index');
    }

    /**
     * Đổi trạng thái khóa/mở của tài khoản.
     */
    public function toggle(int $id): void
    {
        $this->requireRole(['admin']);
        $ok = (new User())->toggleStatus($id);

        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'toggle', 'user', $id, 'Khóa/mở khóa tài khoản');
            \flash('success', 'Đã đổi trạng thái tài khoản.');
        } else {
            \flash('danger', 'Không thể đổi trạng thái tài khoản.');
        }

        \redirect('tai_khoan/index');
    }

    /**
     * Xóa tài khoản khỏi hệ thống.
     */
    public function delete(int $id): void
    {
        $this->requireRole(['admin']);
        if ((int) Auth::user()['id'] === (int) $id) {
            \flash('danger', 'Không thể xóa chính tài khoản Admin đang đăng nhập.');
            \redirect('tai_khoan/index');
        }

        $ok = (new User())->delete($id);
        if ($ok) {
            (new Log())->create(Auth::user()['id'], 'delete', 'user', $id, 'Xóa tài khoản');
            \flash('success', 'Đã xóa tài khoản.');
        } else {
            \flash('danger', 'Xóa tài khoản thất bại.');
        }

        \redirect('tai_khoan/index');
    }
}
