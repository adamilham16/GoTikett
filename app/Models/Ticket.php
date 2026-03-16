<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_id', 'title', 'desc', 'type', 'status', 'approval',
        'category', 'client', 'priority',
        'creator_id', 'assignee_id', 'approved_by',
        'approved_at', 'due_date', 'closed_at',
        'frozen', 'frozen_pct', 'stage_log', 'stage_due',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'due_date'    => 'datetime',
        'closed_at'   => 'datetime',
        'frozen'      => 'boolean',
        'stage_log'   => 'array',
        'stage_due'   => 'array',
    ];

    public const STAGES = [
        'userreq', 'reqanalysis', 'sprintplanning',
        'development', 'sit', 'uat', 'deployment', 'golive',
    ];

    public const STAGE_LABELS = [
        'userreq'       => 'User Req',
        'reqanalysis'   => 'Requirement Analysis',
        'sprintplanning'=> 'Sprint Planning',
        'development'   => 'Development',
        'sit'           => 'SIT',
        'uat'           => 'UAT',
        'deployment'    => 'Deployment',
        'golive'        => 'GO Live',
    ];

    public const STAGE_ICONS = [
        'userreq'       => '📋',
        'reqanalysis'   => '🔎',
        'sprintplanning'=> '📅',
        'development'   => '💻',
        'sit'           => '🔍',
        'uat'           => '👥',
        'deployment'    => '🚀',
        'golive'        => '✅',
    ];

    public const STAGE_COLORS = [
        'userreq'       => '#60a5fa',
        'reqanalysis'   => '#818cf8',
        'sprintplanning'=> '#a78bfa',
        'development'   => '#f5c542',
        'sit'           => '#f472b6',
        'uat'           => '#34d399',
        'deployment'    => '#fb923c',
        'golive'        => '#3dd68c',
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

    // Progress 0-100 berdasarkan task
    public function getProgressAttribute(): int
    {
        // Gunakan relasi yang sudah di-load (eager loaded) untuk hindari N+1
        $tasks = $this->relationLoaded('tasks') ? $this->tasks : $this->tasks()->get();
        $total = $tasks->count();

        if ($total === 0) {
            return 0;
        }

        $done = $tasks->where('status', 'Done')->count();

        return (int) round(($done / $total) * 100);
    }

    // SLA info
    public function getSlaAttribute(): array
    {
        if ($this->status === 'golive') {
            return ['label' => 'Closed', 'cls' => 'sla-ok', 'pct' => 100, 'bar' => 'var(--green)'];
        }
        if (!$this->due_date) {
            return ['label' => 'No SLA', 'cls' => '', 'pct' => 0, 'bar' => 'var(--text3)'];
        }
        $created  = $this->created_at->timestamp;
        $due      = $this->due_date->timestamp;
        $now      = now()->timestamp;
        $total    = max($due - $created, 1);
        $elapsed  = $now - $created;
        $remain   = (int) round(($due - $now) / 86400);
        $pct      = min(100, (int) round(($elapsed / $total) * 100));

        if ($pct >= 100) {
            return ['label' => 'OVERDUE ' . abs($remain) . ' hari', 'cls' => 'sla-over', 'pct' => 100, 'bar' => 'var(--red)'];
        }
        if ($pct >= 75) {
            return ['label' => $remain . ' hari lagi', 'cls' => 'sla-warn', 'pct' => $pct, 'bar' => 'var(--yellow)'];
        }
        return ['label' => $remain > 0 ? $remain . ' hari lagi' : 'Hari ini', 'cls' => 'sla-ok', 'pct' => $pct, 'bar' => 'var(--green)'];
    }

    // Lead time
    public function getLeadTimeAttribute(): string
    {
        if (!$this->closed_at) return '—';
        $days = (int) round(($this->closed_at->timestamp - $this->created_at->timestamp) / 86400);
        return $days . ' hari';
    }

    // Next stage
    public function getNextStageAttribute(): ?string
    {
        $idx = array_search($this->status, self::STAGES);
        return self::STAGES[$idx + 1] ?? null;
    }
}
