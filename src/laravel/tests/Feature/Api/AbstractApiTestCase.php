<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

abstract class AbstractApiTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * Подготавливает тестовый контекст, вызывая родительский метод и выполняя дополнительные настройки.
     * Этот метод вызывается перед каждым тестом и не должен быть реализован в дочерних классах.
     * В дочерних классах нужно реализовать метод setUpTestContext().
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTestContext();
    }

    /**
     * Абстрактный метод для настройки тестового контекста.
     *
     * @return void
     */
    abstract protected function setUpTestContext(): void;

    /**
     * Возвращает тестируемый маршрут.
     *
     * @param mixed ...$args Параметр(ы) для вставки в запрос, если они нужны для динамического маршрута.
     * @return string Маршрут для запроса.
     */
    abstract protected function getRoute(array|string|null $routeParameter = null): string;

    /**
     * Возвращает HTTP-метод (get, post, patch, put, delete) для запроса.
     *
     * @return string Название HTTP-метода для запроса (например, 'get', 'post', 'delete').
     */
    abstract protected function getMethod(): string;

    /**
     * Метод для проверки успешного ответа на запрос. При необходимости можно переопределить.
     *
     * @param TestResponse $response Ответ от тестируемого запроса.
     * @param array $expected Массив с ожидаемыми значениями для проверки.
     * @return void
     */
    protected function checkSuccess(TestResponse $response, array $expected = []): void
    {
        $response->assertStatus(200);
    }

    /**
     * Метод для проверки ошибки в ответе на запрос. При необходимости можно переопределить.
     *
     * @param TestResponse $response Ответ от тестируемого запроса.
     * @param int $status Ожидаемый HTTP-статус код.
     * @param string|null $message Ожидаемое сообщение ошибки (если есть).
     * @return void
     */
    protected function checkError(TestResponse $response, int $status, string $message = null): void
    {
        $response->assertStatus($status);

        if ($message) {
            $response->assertExactJsonStructure([
                'error',
            ]);

            $error = $response->json('error');

            $this->assertIsString($error);
            $this->assertEquals($message, $error);
        }
    }
}

