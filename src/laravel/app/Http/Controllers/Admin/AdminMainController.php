<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminMainController extends Controller
{
    public function index()
    {
        return view('admin.main.index');
    }
}
