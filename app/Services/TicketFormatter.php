<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;

class TicketFormatter
{
    /**
     * Format tiket lengkap (untuk detail view / show endpoint).
     */
    public function formatFull(Ticket $ticket, User $viewer): array
    {
        $sla    = $ticket->sla;
        $freeze = $ticket->currentFreeze;

        return [
            'id'          => $ticket->ticket_id,
            'title'       => $ticket->title,
            'desc'        => $ticket->desc,
            'type'        => $ticket->type,
            'approval'    => $ticket->approval,
            'category'    => $ticket->category,
            'client'      => $ticket->client,
            'creator'     => [
                'name'     => $ticket->creator?->name,
                'initials' => $ticket->creator?->initials,
                'color'    => $ticket->creator?->color,
            ],
            'assignee'    => [
                'id'       => $ticket->assignee_id,
                'name'     => $ticket->assignee?->name,
                'initials' => $ticket->assignee?->initials,
                'color'    => $ticket->assignee?->color,
            ],
            'approved_by' => $ticket->approver?->name,
            'approved_at' => $ticket->approved_at?->format('d M Y H:i'),
            'created_at'  => $ticket->created_at->format('d M Y H:i'),
            'due_date'    => $ticket->due_date?->format('d M Y'),
            'closed_at'   => $ticket->closed_at?->format('d M Y H:i'),
            'lead_time'   => $ticket->lead_time,
            'progress'    => $ticket->progress,
            'sla'         => $sla,
            'can_delete'  => $viewer->isAdmin(),
            'freeze_status'    => $ticket->freeze_status,
            'freeze_id'        => $freeze?->id,
            'freeze_duration'  => $freeze?->duration_days,
            'freeze_reason'    => $freeze?->reason,
            'freeze_requester' => $freeze?->requester?->name,
            'freeze_ends_at'   => $freeze?->freeze_ends_at?->format('d M Y H:i'),
            'tasks'       => $ticket->tasks->map(fn($t) => [
                'id'       => $t->id,
                'title'    => $t->title,
                'status'   => $t->status,
                'due_date' => $t->due_date?->format('d M Y'),
                'notes'    => $t->notes,
            ]),
            'comments'    => $ticket->comments->map(fn($c) => [
                'user'     => $c->user?->name,
                'initials' => $c->user?->initials,
                'color'    => $c->user?->color,
                'text'     => $c->text,
                'time'     => $c->created_at->format('d M Y H:i'),
                'own'      => $c->user_id === $viewer->id,
            ]),
            'attachments' => $ticket->attachments->map(fn($a) => [
                'id'   => $a->id,
                'name' => $a->original_name,
                'size' => $a->formatted_size,
                'icon' => $a->icon,
                'url'  => route('attachments.download', $a->id),
            ]),
        ];
    }

    /**
     * Format ringkasan tiket (untuk list / appData endpoint).
     */
    public function formatSummary(Ticket $t): array
    {
        $sla    = $t->sla;
        $freeze = $t->currentFreeze;

        return [
            'id'                => $t->ticket_id,
            'title'             => $t->title,
            'type'              => $t->type,
            'approval'          => $t->approval,
            'category'          => $t->category,
            'client'            => $t->client,
            'assignee'          => $t->assignee?->name,
            'assignee_color'    => $t->assignee?->color,
            'assignee_initials' => $t->assignee?->initials,
            'creator'           => $t->creator?->name,
            'creator_id'        => $t->creator_id,
            'created_at'        => $t->created_at->toISOString(),
            'due_date'          => $t->due_date?->format('d M Y'),
            'closed_at'         => $t->closed_at?->toISOString(),
            'progress'          => $t->progress,
            'sla'               => $sla,
            'task_total'        => $t->tasks->count(),
            'task_done'         => $t->tasks->where('status', 'Done')->count(),
            'freeze_status'     => $t->freeze_status,
            'freeze_id'         => $freeze?->id,
            'freeze_duration'   => $freeze?->duration_days,
            'freeze_reason'     => $freeze?->reason,
            'freeze_requester'  => $freeze?->requester?->name,
            'freeze_ends_at'    => $freeze?->freeze_ends_at?->format('d M Y'),
        ];
    }
}
