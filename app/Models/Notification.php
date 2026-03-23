<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'message', 'ticket_id', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    /**
     * Buat notifikasi untuk user tertentu.
     */
    public static function send(int $userId, string $type, string $title, string $message, ?string $ticketId = null): void
    {
        static::create([
            'user_id'   => $userId,
            'type'      => $type,
            'title'     => $title,
            'message'   => $message,
            'ticket_id' => $ticketId,
        ]);
    }
}
