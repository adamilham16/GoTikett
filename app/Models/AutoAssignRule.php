<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoAssignRule extends Model
{
    protected $table = 'auto_assign_rules';
    protected $fillable = ['kategori', 'client', 'assignee_id'];

    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assignee_id'); }
}
