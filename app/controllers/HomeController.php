<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;

/**
 * Controller cho phần giao diện public giới thiệu sản phẩm.
 */
class HomeController extends BaseController
{
    public function index(): void
    {
        $this->render('home/index', [], 'Trang chủ');
    }

    public function about(): void
    {
        $this->render('home/about', [], 'Giới thiệu');
    }
}
