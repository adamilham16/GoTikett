<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $fillable = ['user_id', 'token', 'used', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime', 'used' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }
}
