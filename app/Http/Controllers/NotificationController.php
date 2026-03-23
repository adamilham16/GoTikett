<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    /** GET /notifications — ambil notifikasi milik user yang login */
    public function index()
    {
        $userId = session('user_id');

        $notifications = Notification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn($n) => [
                'id'        => $n->id,
                'type'      => $n->type,
                'title'     => $n->title,
                'message'   => $n->message,
                'ticket_id' => $n->ticket_id,
                'read'      => !is_null($n->read_at),
                'time'      => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /** POST /notifications/mark-read — tandai semua sebagai telah dibaca */
    public function markRead()
    {
        Notification::where('user_id', session('user_id'))
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
