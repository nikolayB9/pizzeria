<?php

namespace App\Listeners;

use App\Exceptions\Cart\CartMergeException;
use App\Services\Api\V1\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class MergeGuestCartToUserCart
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $oldSession = session()->pull('old_session_id');

        if (!$oldSession) {
            Log::debug('Не найден old_session_id, пропускаю слияние корзины.');
            return;
        }

        $userId = $event->user->id;

        try {
            $merged = app(CartService::class)->mergeCartFromSessionToUser($oldSession, $userId);
            session()->flash('cart_merge', $merged);

            if ($merged) {
                Log::info("Перенос корзины пользователя [$userId] из сессии [$oldSession].");
            } else {
                Log::debug("Корзина по сессии [$oldSession] пуста. Перенос не выполнялся.");
            }
        } catch (CartMergeException $e) {
            session()->flash('cart_merge', false);
            Log::warning("Не удалось перенести корзину пользователя [$userId] из сессии [$oldSession].", [
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
