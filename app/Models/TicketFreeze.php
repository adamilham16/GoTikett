<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketFreeze extends Model
{
    protected $fillable = [
        'ticket_id', 'requested_by', 'duration_days', 'reason', 'status',
        'approved_by', 'rejected_by',
        'approved_at', 'rejected_at', 'freeze_starts_at', 'freeze_ends_at',
    ];

    protected $casts = [
        'approved_at'      => 'datetime',
        'rejected_at'      => 'datetime',
        'freeze_starts_at' => 'datetime',
        'freeze_ends_at'   => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
