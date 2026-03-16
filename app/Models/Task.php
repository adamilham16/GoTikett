<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = ['ticket_id', 'title', 'status'];

    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
}
