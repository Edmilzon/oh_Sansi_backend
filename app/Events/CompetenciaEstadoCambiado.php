<?php

namespace App\Events;

use App\Model\Competencia;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetenciaEstadoCambiado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Competencia $competencia,
        public string $nuevoEstado
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('competencia.' . $this->competencia->id_competencia),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CompetenciaEstadoCambiado';
    }

    public function broadcastWith(): array
    {
        return [
            'id_competencia' => $this->competencia->id_competencia,
            'estado_fase'    => $this->nuevoEstado,
            'timestamp'      => now()->toIso8601String()
        ];
    }
}
