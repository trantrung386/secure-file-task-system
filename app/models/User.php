<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Model thao tác dữ liệu bảng tài khoản.
 */
class User extends BaseModel
{
    /**
     * Lấy toàn bộ tài khoản để Admin quản lý.
     */
    public function all(): array
    {
        return $this->db->query('SELECT * FROM tai_khoan ORDER BY created_at DESC, id DESC')->fetchAll();
    }

    /**
     * Lấy danh sách nhân viên và trưởng nhóm đang hoạt động.
     */
    public function allActiveEmployees(): array
    {
        $stmt = $this->db->prepare("SELECT id, name, email FROM tai_khoan WHERE role = 'employee' AND status = 'active' ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Tìm tài khoản theo ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tai_khoan WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Tìm tài khoản theo email khi đăng nhập hoặc kiểm tra trùng.
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tai_khoan WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Tạo tài khoản mới và hash mật khẩu trước khi lưu.
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tai_khoan(name, email, password, role, department, status, created_at)
             VALUES(:name, :email, :password, :role, :department, :status, NOW())'
        );

        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_BCRYPT),
            'role' => $data['role'],
            'department' => $data['department'],
            'status' => $data['status'],
        ]);
    }

    /**
     * Cập nhật tài khoản, có hỗ trợ đổi mật khẩu nếu người dùng nhập mật khẩu mới.
     */
    public function update(int $id, array $data): bool
    {
        $fields = [
            'name = :name',
            'email = :email',
            'role = :role',
            'department = :department',
            'status = :status',
        ];

        $params = [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'department' => $data['department'],
            'status' => $data['status'],
        ];

        if (!empty($data['password'])) {
            $fields[] = 'password = :password';
            $params['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql = 'UPDATE tai_khoan SET ' . implode(', ', $fields) . ' WHERE id = :id';
        return $this->db->prepare($sql)->execute($params);
    }

    /**
     * Xóa tài khoản khỏi hệ thống.
     */
    public function delete(int $id): bool
    {
        return $this->db->prepare('DELETE FROM tai_khoan WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * Đổi trạng thái active/inactive của tài khoản.
     */
    public function toggleStatus(int $id): bool
    {
        $user = $this->find($id);
        if (!$user) {
            return false;
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        return $this->db->prepare('UPDATE tai_khoan SET status = :status WHERE id = :id')
            ->execute(['status' => $newStatus, 'id' => $id]);
    }
}
