<?php

namespace App\Services\Traits;

trait ArrayValidationTrait
{
    /**
     * Проверяет массив (например, ответ от API) на наличие обязательных ключей и соответствие значений.
     *
     * @param array<string, mixed> $array Массив с данными (обычно — ответ от внешнего сервиса).
     * @param array<string> $requiredKeys Ожидаемые ключи с поддержкой dot-нотации (пример: ['id', 'meta.order_id']).
     * @param array<string, mixed> $expectedValues Ожидаемые значения ключей (пример: ['meta.order_id' => '123']).
     *
     * @return array<string> Список ошибок в формате строк. Если ошибок нет — возвращается пустой массив.
     */
    protected function validateRequiredKeysAndValues(array $array,
                                                     array $requiredKeys,
                                                     array $expectedValues = []): array
    {
        $errors = [];

        foreach ($requiredKeys as $key) {
            $exists = true;
            $value = null;

            if (str_contains($key, '.')) {
                $keys = explode('.', trim($key));
                $arr = $array;
                foreach ($keys as $k) {
                    if (!is_array($arr) || !array_key_exists($k, $arr)) {
                        $errors[] = "Отсутствует обязательный ключ [$key].";
                        $exists = false;
                        break;
                    }
                    $arr = $arr[$k];
                }
                $value = $arr;
            } elseif (!array_key_exists($key, $array)) {
                $errors[] = "Отсутствует обязательный ключ [$key].";
                $exists = false;
            } else {
                $value = $array[$key];
            }

            // Проверка на соответствие ожидаемому значению
            if (array_key_exists($key, $expectedValues) && $exists
                && $value !== null && $value !== $expectedValues[$key]) {
                $errors[] = "Полученное значение [$key], равное [$value], не равно ожидаемому:
                    [{$expectedValues[$key]}].";
            }
        }

        return $errors;
    }
}
