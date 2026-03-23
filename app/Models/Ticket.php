<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_id', 'title', 'desc', 'type', 'approval',
        'category', 'client', 'priority',
        'creator_id', 'assignee_id', 'approved_by',
        'approved_at', 'due_date', 'closed_at',
        'freeze_status', 'freeze_paused_seconds',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'due_date'    => 'datetime',
        'closed_at'   => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function freezes(): HasMany
    {
        return $this->hasMany(TicketFreeze::class);
    }

    // Freeze aktif saat ini (status = approved)
    public function activeFreeze(): HasOne
    {
        return $this->hasOne(TicketFreeze::class)
                    ->where('status', 'approved')
                    ->latest();
    }

    // Freeze aktif atau pending approval (untuk ditampilkan di UI)
    public function currentFreeze(): HasOne
    {
        return $this->hasOne(TicketFreeze::class)
                    ->whereIn('status', ['pending_approval', 'approved'])
                    ->latest();
    }

    // Progress 0-100 berdasarkan task
    public function getProgressAttribute(): int
    {
        $tasks = $this->relationLoaded('tasks') ? $this->tasks : $this->tasks()->get();
        $total = $tasks->count();

        if ($total === 0) {
            return 0;
        }

        $done = $tasks->where('status', 'Done')->count();

        return (int) round(($done / $total) * 100);
    }

    // SLA info — tenggat diambil dari due_date task paling akhir (fallback: due_date tiket)
    // Catatan: due_date sudah di-extend saat freeze disetujui (via approveFreeze)
    public function getSlaAttribute(): array
    {
        if ($this->closed_at) {
            return ['label' => 'Closed', 'cls' => 'sla-ok', 'pct' => 100, 'bar' => 'var(--green)', 'due' => $this->due_date?->format('d M Y') ?? '—', 'frozen' => false];
        }

        $tasks       = $this->relationLoaded('tasks') ? $this->tasks : $this->tasks()->get();
        $latestTask  = $tasks->whereNotNull('due_date')->max('due_date');
        $effectiveDue = $latestTask
            ? \Carbon\Carbon::parse($latestTask)
            : $this->due_date;

        if (!$effectiveDue) {
            return ['label' => 'No SLA', 'cls' => '', 'pct' => 0, 'bar' => 'var(--text3)', 'due' => '—', 'frozen' => false];
        }

        $created       = $this->created_at->timestamp;
        $due           = $effectiveDue->timestamp;
        $total         = max($due - $created, 1);
        // freeze_paused_seconds = total detik yang BENAR-BENAR dijeda (dari freeze yang sudah selesai)
        // digunakan untuk menghitung elapsed aktual (tidak menghitung waktu beku)
        $pausedSeconds = (int) ($this->freeze_paused_seconds ?? 0);

        // Tenggat yang ditampilkan — due_date sudah di-extend saat freeze disetujui
        $dueFormatted = $effectiveDue->format('d M Y');

        // Jika sedang aktif freeze: SLA beku sejak freeze_starts_at
        if ($this->freeze_status === 'active') {
            $activeFreeze = $this->relationLoaded('activeFreeze')
                ? $this->activeFreeze
                : $this->activeFreeze()->first();
            $effectiveNow = $activeFreeze?->freeze_starts_at?->timestamp ?? now()->timestamp;
            $elapsed = ($effectiveNow - $created) - $pausedSeconds;
            $pct     = min(100, max(0, (int) round(($elapsed / $total) * 100)));
            $endsAt  = $activeFreeze?->freeze_ends_at?->format('d M Y') ?? '—';
            return ['label' => '🧊 Dijeda s/d ' . $endsAt, 'cls' => 'sla-freeze', 'pct' => $pct, 'bar' => 'var(--purple)', 'due' => $dueFormatted, 'frozen' => true];
        }

        // Jika sedang request freeze (pending approval): tampilkan info tapi SLA tetap jalan
        $now     = now()->timestamp;
        $elapsed = ($now - $created) - $pausedSeconds;
        // due_date sudah di-extend — tidak perlu tambah pausedSeconds lagi
        $remain  = (int) round(($due - $now) / 86400);
        $pct     = min(100, max(0, (int) round(($elapsed / $total) * 100)));

        if ($this->freeze_status === 'pending_approval') {
            $label = ($pct >= 100 ? 'OVERDUE ' . abs($remain) . ' hari' : ($remain > 0 ? $remain . ' hari lagi' : 'Hari ini')) . ' ⏸';
            return ['label' => $label, 'cls' => 'sla-warn', 'pct' => $pct, 'bar' => 'var(--yellow)', 'due' => $dueFormatted, 'frozen' => false];
        }

        if ($pct >= 100) {
            return ['label' => 'OVERDUE ' . abs($remain) . ' hari', 'cls' => 'sla-over', 'pct' => 100, 'bar' => 'var(--red)', 'due' => $dueFormatted, 'frozen' => false];
        }
        if ($pct >= 75) {
            return ['label' => $remain . ' hari lagi', 'cls' => 'sla-warn', 'pct' => $pct, 'bar' => 'var(--yellow)', 'due' => $dueFormatted, 'frozen' => false];
        }
        return ['label' => $remain > 0 ? $remain . ' hari lagi' : 'Hari ini', 'cls' => 'sla-ok', 'pct' => $pct, 'bar' => 'var(--green)', 'due' => $dueFormatted, 'frozen' => false];
    }

    // Lead time
    public function getLeadTimeAttribute(): string
    {
        if (!$this->closed_at) return '—';
        $days = (int) round(($this->closed_at->timestamp - $this->created_at->timestamp) / 86400);
        return $days . ' hari';
    }
}
