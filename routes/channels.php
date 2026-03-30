<?php

use Illuminate\Support\Facades\Broadcast;

/*
 * Gunakan middleware 'web' agar session tersedia di broadcasting auth endpoint.
 * App ini pakai custom session (bukan Laravel Auth), jadi tidak pakai middleware 'auth'.
 */
Broadcast::routes(['middleware' => ['web', 'auth.session']]);

Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return $user && (int) $user->id === (int) $userId;
});
