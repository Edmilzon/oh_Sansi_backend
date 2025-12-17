<?php

namespace App\Events;

use App\Model\Examen;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamenCreado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Examen $examen) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('competencia.' . $this->examen->id_competencia),
        ];
    }

    public function broadcastAs(): string
    {
        return 'examen.creado';
    }
}
