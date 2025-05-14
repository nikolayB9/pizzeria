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
     * @param mixed $routeParameter Параметр(ы) для вставки в запрос, если они нужны для динамического маршрута.
     * @return string Маршрут для запроса.
     */
    abstract protected function getRoute(mixed $routeParameter = null): string;

    /**
     * Возвращает HTTP-метод (get, post, patch, put, delete) для запроса.
     *
     * @return string Название HTTP-метода для запроса (например, 'get', 'post', 'delete').
     */
    abstract protected function getMethod(): string;

    /**
     * Метод для проверки успешного ответа на запрос.
     *
     * @param TestResponse $response Ответ от тестируемого запроса.
     * @param mixed $data Ожидаемые основные данные для проверки (если есть).
     * @param mixed $meta Ожидаемые дополнительные данные для проверки (если есть).
     * @param int $status Ожидаемый HTTP-статус код.
     * @return void
     */
    protected function checkSuccess(TestResponse $response, mixed $data = [], mixed $meta = [], int $status = 200): void
    {
        $response->assertStatus($status);

        $response->assertExactJsonStructure([
            'success',
            'data',
            'meta',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals($data, $response->json('data'));
        $this->assertIsArray($response->json('meta'));
        $this->assertEquals($meta, $response->json('meta'));
    }

    /**
     * Метод для проверки ошибки в ответе на запрос.
     *
     * @param TestResponse $response Ответ от тестируемого запроса.
     * @param int $status Ожидаемый HTTP-статус код.
     * @param string $message Ожидаемое сообщение ошибки.
     * @param array $errors Дополнительные ошибки (если есть).
     * @return void
     */
    protected function checkError(TestResponse $response,
                                  int          $status,
                                  string       $message,
                                  array        $errors = []): void
    {
        $response->assertStatus($status);

        $response->assertExactJsonStructure([
            'success',
            'message',
            'errors',
        ]);

        $this->assertFalse($response->json('success'));
        $this->assertIsString($response->json('message'));
        $this->assertEquals($message, $response->json('message'));
        $this->assertIsArray($response->json('errors'));

        if (!empty($errors)) {
            $this->assertEquals($errors, $response->json('errors'));
        }
    }
}

