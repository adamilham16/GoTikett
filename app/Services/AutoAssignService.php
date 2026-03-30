<?php

namespace App\Services;

use App\Models\AutoAssignRule;

class AutoAssignService
{
    /**
     * Cari assignee_id berdasarkan kategori dan client.
     * Kembalikan null jika tidak ada rule yang cocok.
     */
    public function findAssigneeId(string $category, string $client): ?int
    {
        $rule = AutoAssignRule::where('kategori', $category)
            ->where('client', $client)
            ->first();

        return $rule?->assignee_id;
    }
}
