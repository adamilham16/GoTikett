<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TicketQueryService
{
    /**
     * Kembalikan base query Ticket yang sudah di-scope sesuai role user.
     * Pemanggil bisa chain dengan with(), withCount(), limit(), dll.
     */
    public function scopeForUser(User $user): Builder
    {
        $query = Ticket::query()->orderByDesc('created_at');

        match ($user->type) {
            'user'       => $query->where('creator_id', $user->id),
            'it'         => $query->where('assignee_id', $user->id),
            'manager'    => $query->whereIn('creator_id',
                                User::where('approver_id', $user->id)->select('id')),
            'it_manager' => $query->whereIn('assignee_id',
                                User::where('type', 'it')->select('id')),
            default      => null, // superadmin / role lain melihat semua
        };

        return $query;
    }
}
