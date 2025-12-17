<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SistemaEstadoActualizado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * El payload con la foto del sistema.
     * Public para que Laravel lo serialice automáticamente en el JSON del evento.
     */
    public array $snapshot;

    public function __construct(array $snapshot)
    {
        $this->snapshot = $snapshot;
    }

    /**
     * Canal público donde escucharán todos los clientes.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sistema-global'),
        ];
    }

    /**
     * Nombre del evento que escuchará el frontend (ej: .estado.actualizado)
     */
    public function broadcastAs(): string
    {
        return 'estado.actualizado';
    }
}
