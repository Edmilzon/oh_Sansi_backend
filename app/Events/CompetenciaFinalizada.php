<?php

namespace App\Events;

use App\Model\Competencia;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetenciaFinalizada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Competencia $competencia
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('competencia.' . $this->competencia->id_competencia),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id_competencia' => $this->competencia->id_competencia,
            'estado' => $this->competencia->estado_fase,
            'mensaje' => 'Resultados calculados y disponibles.',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CompetenciaFinalizada';
    }
}
