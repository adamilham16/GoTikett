<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use App\Models\TicketFreeze;
use App\Models\Comment;
use App\Models\Notification;

// Backup database harian jam 02:00
Schedule::command('backup:database')
    ->dailyAt('02:00')
    ->name('backup:database')
    ->withoutOverlapping();

// Hapus login_logs yang lebih dari 90 hari
Schedule::call(function () {
    DB::table('login_logs')
        ->where('created_at', '<', now()->subDays(90))
        ->delete();
})->daily()->name('cleanup:login-logs')->withoutOverlapping();

// Hapus password_reset_tokens yang sudah expired atau sudah digunakan (lebih dari 7 hari)
Schedule::call(function () {
    DB::table('password_reset_tokens')
        ->where(function ($q) {
            $q->where('used', true)
              ->orWhere('expires_at', '<', now());
        })
        ->where('created_at', '<', now()->subDays(7))
        ->delete();
})->daily()->name('cleanup:password-reset-tokens')->withoutOverlapping();

// Auto-complete freeze yang sudah melewati freeze_ends_at
Schedule::call(function () {
    $expired = TicketFreeze::with('ticket')
        ->where('status', 'approved')
        ->where('freeze_ends_at', '<', now())
        ->get();

    foreach ($expired as $freeze) {
        $ticket = $freeze->ticket;
        if (!$ticket) continue;

        $pausedSeconds = (int) round(
            $freeze->freeze_ends_at->timestamp - $freeze->freeze_starts_at->timestamp
        );

        $freeze->update(['status' => 'completed']);
        $ticket->update([
            'freeze_status'         => null,
            'freeze_paused_seconds' => $ticket->freeze_paused_seconds + $pausedSeconds,
        ]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $freeze->requested_by,
            'text'      => "⏰ Periode freeze selesai (durasi: {$freeze->duration_days} hari). Tiket otomatis diaktifkan kembali. SLA dilanjutkan.",
        ]);

        // Notifikasi ke requester & creator
        $recipients = collect([$freeze->requested_by, $ticket->creator_id])->filter()->unique();
        foreach ($recipients as $recipientId) {
            Notification::send(
                $recipientId,
                'freeze_ended',
                'Tiket Aktif Kembali ⏰',
                "Periode freeze tiket {$ticket->ticket_id} ({$ticket->title}) telah berakhir. SLA dilanjutkan secara otomatis.",
                $ticket->ticket_id
            );
        }
    }
})->hourly()->name('process:expired-freezes')->withoutOverlapping();
