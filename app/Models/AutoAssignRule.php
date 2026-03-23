<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class AutoAssignRule extends Model
{
    protected $table = 'auto_assign_rules';
    protected $fillable = ['kategori', 'client', 'assignee_id'];

    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assignee_id'); }

    public static function allCached(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('auto_assign_rules_all', 3600, function () {
            return static::with('assignee')->get();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('auto_assign_rules_all');
    }
}
