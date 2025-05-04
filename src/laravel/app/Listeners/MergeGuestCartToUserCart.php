<?php

namespace App\Listeners;

use App\Services\Api\V1\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
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
            Log::debug('No old session ID found, skipping cart merge.');
            return;
        }

        $userId = $event->user->id;

        app(CartService::class)->mergeCartFromSessionToUser($oldSession, $userId);

        Log::info("Cart merged for user {$userId} from session {$oldSession}");
    }
}
