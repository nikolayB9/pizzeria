<?php

namespace App\Enums\Error;

enum ErrorMessageEnum: string
{
    case VALIDATION = 'Ошибка валидации.';
    case UNAUTHORIZED = 'Не авторизован.';
    case TYPE_ERROR = 'Неверный тип параметра.';
    case ERROR = 'Внутренняя ошибка сервера';
}
