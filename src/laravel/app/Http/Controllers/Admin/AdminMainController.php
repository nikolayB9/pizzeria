<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AdminMainController extends Controller
{
    /**
     * Отображает главную страницу панели администратора.
     *
     * @return View Представление главной страницы.
     */
    public function index(): View
    {
        return view('admin.main.index');
    }
}
