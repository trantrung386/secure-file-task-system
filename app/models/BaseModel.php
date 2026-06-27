<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Model gốc chứa kết nối PDO dùng chung cho các model con.
 */
abstract class BaseModel
{
    protected PDO $db;

    /**
     * Khi khởi tạo model sẽ tự động lấy kết nối CSDL.
     */
    public function __construct()
    {
        $this->db = Database::connection();
    }
}
