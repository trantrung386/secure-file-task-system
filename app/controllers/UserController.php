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

        $validator = \App\Core\Validator::make($_POST, [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|string|min:6|max:100',
            'role' => 'required|in:admin,manager,employee',
            'department' => 'string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            \flash('danger', $validator->firstError());
            \redirect('tai_khoan/create');
        }

        $validated = $validator->validated();
        $model = new User();
        $email = $validated['email'];

        if ($model->findByEmail($email)) {
            \flash('danger', 'Email đã tồn tại.');
            \redirect('tai_khoan/create');
        }

        $ok = $model->create([
            'name' => $validated['name'],
            'email' => $email,
            'password' => (string) ($_POST['password'] ?? ''),
            'role' => $validated['role'],
            'department' => $validated['department'] ?? '',
            'status' => $validated['status'],
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
        if (!$user) {
            \flash('danger', 'Không tìm thấy tài khoản.');
            \redirect('tai_khoan/index');
        }
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

        $userModel = new User();
        $user = $userModel->find($id);
        if (!$user) {
            \flash('danger', 'Không tìm thấy tài khoản.');
            \redirect('tai_khoan/index');
        }

        $rules = [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:150',
            'role' => 'required|in:admin,manager,employee',
            'department' => 'string|max:100',
            'status' => 'required|in:active,inactive',
        ];
        if (!empty($_POST['password'])) {
            $rules['password'] = 'string|min:6|max:100';
        }

        $validator = \App\Core\Validator::make($_POST, $rules);
        if ($validator->fails()) {
            \flash('danger', $validator->firstError());
            \redirect('tai_khoan/edit', ['id' => $id]);
        }

        $validated = $validator->validated();
        $existing = $userModel->findByEmail($validated['email']);
        if ($existing && (int) $existing['id'] !== $id) {
            \flash('danger', 'Email đã được sử dụng bởi tài khoản khác.');
            \redirect('tai_khoan/edit', ['id' => $id]);
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'department' => $validated['department'] ?? '',
            'status' => $validated['status'],
        ];
        if (!empty($_POST['password'])) {
            $payload['password'] = (string) $_POST['password'];
        }

        $ok = $userModel->update($id, $payload);

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
