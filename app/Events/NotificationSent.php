<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $userId,
        public readonly string $type,
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $ticketId = null,
        public readonly ?int   $notifId   = null,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("notifications.{$this->userId}")];
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->notifId,
            'type'      => $this->type,
            'title'     => $this->title,
            'message'   => $this->message,
            'ticket_id' => $this->ticketId,
            'time'      => now()->diffForHumans(),
        ];
    }
}
