<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Формирует успешный JSON-ответ для API.
     *
     * @param mixed $data Основные данные ответа (массив, объект, строка и т.п.).
     * @param int $status HTTP-статус ответа (по умолчанию 200).
     * @param array $meta Дополнительные метаданные (например, пагинация).
     * @return JsonResponse
     */
    public static function success(mixed $data = [], int $status = 200, array $meta = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    /**
     * Формирует ошибочный JSON-ответ для API.
     *
     * @param string $message Сообщение об ошибке.
     * @param int $status HTTP-статус ошибки.
     * @param array $errors Дополнительные детали ошибки (например, ошибки валидации).
     * @return JsonResponse
     */
    public static function fail(string $message, int $status, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}

