<?php

namespace App\Events;

use App\Model\Examen;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamenEstadoCambiado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Examen $examen,
        public string $nuevoEstado
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('competencia.' . $this->examen->id_competencia),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ExamenEstadoCambiado';
    }

    public function broadcastWith(): array
    {
        return [
            'id_examen'        => $this->examen->id_examen,
            'id_competencia'   => $this->examen->id_competencia,
            'estado_ejecucion' => $this->nuevoEstado,
            'fecha_inicio_real'=> $this->examen->fecha_inicio_real,
            'updated_at'       => now()->toIso8601String(),
        ];
    }
}
