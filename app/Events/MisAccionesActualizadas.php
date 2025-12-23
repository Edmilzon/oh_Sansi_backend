<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MisAccionesActualizadas implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public array $acciones
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('usuario.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MisAccionesActualizadas';
    }

    public function broadcastWith(): array
    {
        return [
            'acciones' => $this->acciones
        ];
    }
}
