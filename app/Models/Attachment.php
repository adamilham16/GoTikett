<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'original_name', 'stored_name', 'mime_type', 'size'];

    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function getIconAttribute(): string
    {
        $icons = [
            'pdf' => '📄', 'doc' => '📝', 'docx' => '📝',
            'xls' => '🗂️', 'xlsx' => '🗂️', 'ppt' => '📑', 'pptx' => '📑',
            'txt' => '📃', 'csv' => '🗂️', 'png' => '🖼️',
            'jpg' => '🖼️', 'jpeg' => '🖼️', 'gif' => '🖼️',
            'zip' => '🗜️', 'rar' => '🗜️',
        ];
        $ext = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
        return $icons[$ext] ?? '📎';
    }
}
