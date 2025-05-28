<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * Отображает страницу входа для администратора.
     *
     * @return View Представление формы входа.
     */
    public function create(): View
    {
        return view('admin.auth.login');
    }

    /**
     * Выполняет аутентификацию и перенаправляет на главную страницу при успехе.
     *
     * @param LoginRequest $request Валидированные данные запроса (email и password).
     *
     * @return RedirectResponse Редирект на главную страницу.
     * @throws ValidationException Если учетные данные недействительны.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->route('main');
    }

    /**
     * Выход администратора из системы. Завершает текущую сессию и перенаправляет на страницу входа.
     *
     * @param Request $request Текущий HTTP-запрос.
     *
     * @return RedirectResponse Редирект на страницу входа.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login.create');
    }
}
