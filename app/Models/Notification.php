<?php

namespace App\Models;

use App\Events\NotificationSent;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'message', 'ticket_id', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    /**
     * Buat notifikasi untuk user tertentu dan broadcast via Reverb.
     */
    public static function send(int $userId, string $type, string $title, string $message, ?string $ticketId = null): void
    {
        $notif = static::create([
            'user_id'   => $userId,
            'type'      => $type,
            'title'     => $title,
            'message'   => $message,
            'ticket_id' => $ticketId,
        ]);

        event(new NotificationSent($userId, $type, $title, $message, $ticketId, $notif->id));
    }

    /**
     * Buat notifikasi sekaligus untuk banyak user (single INSERT) dan broadcast.
     *
     * @param int[] $userIds
     */
    public static function sendMany(array $userIds, string $type, string $title, string $message, ?string $ticketId = null): void
    {
        if (empty($userIds)) {
            return;
        }

        $now  = now();
        $rows = array_map(fn($id) => [
            'user_id'    => $id,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'ticket_id'  => $ticketId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $userIds);

        static::insert($rows);

        // Broadcast ke setiap penerima
        foreach ($userIds as $userId) {
            event(new NotificationSent($userId, $type, $title, $message, $ticketId));
        }
    }
}
