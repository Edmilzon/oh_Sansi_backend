<?php

namespace App\Events;

use App\Model\Competencia;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetenciaCreada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Competencia $competencia) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('area-nivel.' . $this->competencia->id_area_nivel),
        ];
    }

    public function broadcastAs(): string
    {
        return 'competencia.creada';
    }
}
