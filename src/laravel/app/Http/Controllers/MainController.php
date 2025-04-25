<?php

namespace App\Http\Controllers;

use App\Models\User;

class MainController extends Controller
{
    public function index()
    {
        return view('admin.main.index');
    }
}
