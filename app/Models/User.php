<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    protected $fillable = [
        'username', 'name', 'email', 'password', 'type', 'role', 'dept', 'color', 'approver_id', 'is_active',
    ];

    protected $hidden = ['password'];

    protected $casts = ['is_active' => 'boolean'];

    // Relasi ke tiket yang dibuat user ini
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'creator_id');
    }

    // Tiket yang di-assign ke user ini (IT)
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assignee_id');
    }

    // Approver user ini (Dept Head)
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    // Inisial nama (maks 2 huruf)
    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name);
        return strtoupper(substr(implode('', array_map(fn($p) => $p !== '' ? $p[0] : '', $parts)), 0, 2));
    }

    public function isAdmin(): bool
    {
        return $this->type === 'it';
    }

    public function isManager(): bool
    {
        return $this->type === 'manager';
    }
}
