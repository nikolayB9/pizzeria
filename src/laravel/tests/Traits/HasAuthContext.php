<?php

namespace Tests\Traits;

use Tests\Helpers\UserHelper;

trait HasAuthContext
{
    /** Идентификатор сессии (для гостей) */
    protected ?string $sessionId = null;

    /** Идентификатор пользователя (для авторизованных) */
    protected ?int $userId = null;

    /**
     * Возвращает маршрут, GET-запрос к которому инициирует сессию.
     *
     * @return string
     */
    abstract protected function getRequestToSessionStart(): string;

    /**
     * Устанавливает идентификатор сессии, если он ещё не установлен.
     *
     * @return void
     */
    protected function setSessionId(): void
    {
        if (empty($this->sessionId)) {
            $this->get($this->getRequestToSessionStart());
            $this->sessionId = session()->getId();
        }
    }

    /**
     * Устанавливает идентификатор пользователя, если он ещё не установлен.
     *
     * @return void
     */
    protected function setUserId(): void
    {
        if (empty($this->userId)) {
            $user = UserHelper::createUser();
            $this->userId = $user->id;
            $this->actingAs($user);
        }
    }

    /**
     * Возвращает массив авторизации: session_id или user_id в зависимости от контекста.
     *
     * @param 'session'|'user'|'auto' $prefer Явное указание на тип авторизации: session|user|auto.
     * @return array{field: 'user_id'|'session_id', value: string} Массив с типом идентификации пользователя.
     */
    protected function getAuthField(string $prefer = 'auto'): array
    {
        return match ($prefer) {
            'session' => ['field' => 'session_id', 'value' => $this->sessionId],
            'user' => ['field' => 'user_id', 'value' => $this->userId],
            default => $this->userId
                ? ['field' => 'user_id', 'value' => $this->userId]
                : ['field' => 'session_id', 'value' => $this->sessionId],
        };
    }
}

