<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class AbstractAdminTestCase extends TestCase
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
     *
     * @return string Маршрут для запроса.
     */
    abstract protected function getRoute(mixed $routeParameter = null): string;

    /**
     * Возвращает HTTP-метод (get, post, patch, put, delete) для запроса.
     *
     * @return string Название HTTP-метода для запроса (например, 'get', 'post', 'delete').
     */
    abstract protected function getMethod(): string;
}
