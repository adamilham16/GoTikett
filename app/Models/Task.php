<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = ['ticket_id', 'title', 'status', 'due_date', 'notes'];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
}
