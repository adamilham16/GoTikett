<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\TicketFreeze;
use App\Models\User;

class FreezeService
{
    /**
     * IT mengajukan request freeze ke manager.
     */
    public function request(Ticket $ticket, User $requester, int $durationDays, string $reason): TicketFreeze
    {
        $freeze = TicketFreeze::create([
            'ticket_id'     => $ticket->id,
            'requested_by'  => $requester->id,
            'duration_days' => $durationDays,
            'reason'        => $reason,
            'status'        => 'pending_approval',
        ]);

        $ticket->update(['freeze_status' => 'pending_approval']);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $requester->id,
            'text'      => "⏸ Request Pending/Freeze {$durationDays} hari oleh {$requester->name}.\nAlasan: {$reason}\nMenunggu persetujuan manager.",
        ]);

        $managerIds = User::where('type', 'manager')->pluck('id')->all();
        Notification::sendMany(
            $managerIds,
            'freeze_requested',
            'Request Freeze Tiket ⏸',
            "{$requester->name} mengajukan freeze {$durationDays} hari untuk tiket {$ticket->ticket_id} ({$ticket->title}). Alasan: {$reason}",
            $ticket->ticket_id
        );

        return $freeze;
    }

    /**
     * Manager menyetujui freeze — SLA dijeda, due_date diperpanjang.
     */
    public function approve(TicketFreeze $freeze, User $approver): void
    {
        $now        = now();
        $freezeEnds = $now->copy()->addDays($freeze->duration_days);

        $freeze->update([
            'status'           => 'approved',
            'approved_by'      => $approver->id,
            'approved_at'      => $now,
            'freeze_starts_at' => $now,
            'freeze_ends_at'   => $freezeEnds,
        ]);

        $ticket = $freeze->ticket;
        $ticket->update([
            'freeze_status' => 'active',
            'due_date'      => $ticket->due_date->addDays($freeze->duration_days),
        ]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $approver->id,
            'text'      => "✅ Request freeze disetujui oleh {$approver->name}.\nTiket di-freeze hingga {$freezeEnds->format('d M Y H:i')}. SLA dihentikan sementara.",
        ]);

        $recipients = collect([$freeze->requested_by, $ticket->creator_id])->filter()->unique();
        foreach ($recipients as $recipientId) {
            Notification::send(
                $recipientId,
                'freeze_approved',
                'Freeze Disetujui ✅',
                "Freeze tiket {$ticket->ticket_id} ({$ticket->title}) disetujui oleh {$approver->name}. Freeze aktif hingga {$freezeEnds->format('d M Y')}.",
                $ticket->ticket_id
            );
        }
    }

    /**
     * Manager menolak freeze — SLA tetap berjalan normal.
     */
    public function reject(TicketFreeze $freeze, User $rejector): void
    {
        $freeze->update([
            'status'      => 'rejected',
            'rejected_by' => $rejector->id,
            'rejected_at' => now(),
        ]);

        $ticket = $freeze->ticket;
        $ticket->update(['freeze_status' => null]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $rejector->id,
            'text'      => "❌ Request freeze ditolak oleh {$rejector->name}. SLA tetap berjalan normal.",
        ]);

        $recipients = collect([$freeze->requested_by, $ticket->creator_id])->filter()->unique();
        foreach ($recipients as $recipientId) {
            Notification::send(
                $recipientId,
                'freeze_rejected',
                'Freeze Ditolak ❌',
                "Request freeze tiket {$ticket->ticket_id} ({$ticket->title}) ditolak oleh {$rejector->name}. SLA tetap berjalan.",
                $ticket->ticket_id
            );
        }
    }

    /**
     * IT mengakhiri freeze lebih awal — sisa waktu dikembalikan ke SLA.
     */
    public function unfreeze(Ticket $ticket, User $actor): void
    {
        $activeFreeze = TicketFreeze::where('ticket_id', $ticket->id)
            ->where('status', 'approved')
            ->latest()
            ->firstOrFail();

        $pausedSeconds    = (int) round(now()->timestamp - $activeFreeze->freeze_starts_at->timestamp);
        $requestedSeconds = (int) ($activeFreeze->duration_days * 86400);
        $unusedSeconds    = max(0, $requestedSeconds - $pausedSeconds);

        $activeFreeze->update(['status' => 'completed']);
        $ticket->update([
            'freeze_status'         => null,
            'freeze_paused_seconds' => $ticket->freeze_paused_seconds + $pausedSeconds,
            'due_date'              => $unusedSeconds > 0
                ? $ticket->due_date->subSeconds($unusedSeconds)
                : $ticket->due_date,
        ]);

        Comment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $actor->id,
            'text'      => "▶ Tiket diaktifkan kembali oleh {$actor->name}. SLA dilanjutkan (total dijeda: " . round($pausedSeconds / 86400, 1) . " hari).",
        ]);

        $recipients = collect([$activeFreeze->requested_by, $ticket->creator_id])
            ->filter(fn($id) => $id && $id !== $actor->id)
            ->unique();
        foreach ($recipients as $recipientId) {
            Notification::send(
                $recipientId,
                'freeze_ended',
                'Tiket Aktif Kembali ▶',
                "Tiket {$ticket->ticket_id} ({$ticket->title}) telah diaktifkan kembali oleh {$actor->name}. SLA dilanjutkan.",
                $ticket->ticket_id
            );
        }
    }
}
