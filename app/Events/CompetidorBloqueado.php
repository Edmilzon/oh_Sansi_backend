<?php

namespace App\Events;

use App\Model\Evaluacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetidorBloqueado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Evaluacion
     */
    public $evaluacion;

    /**
     * Create a new event instance.
     *
     * @param Evaluacion $evaluacion
     */
    public function __construct(Evaluacion $evaluacion)
    {
        $this->evaluacion = $evaluacion;
    }

    /**
     * Get the channels the event should broadcast on.
     * Se transmite en el canal privado del EXAMEN específico.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('examen.' . $this->evaluacion->id_examen),
        ];
    }

    /**
     * El nombre del evento que escuchará el cliente (Echo).
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'CompetidorBloqueado';
    }

    /**
     * Get the data to broadcast.
     * Payload que recibe el Frontend.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        if (!$this->evaluacion->relationLoaded('usuarioBloqueo')) {
            $this->evaluacion->load('usuarioBloqueo.persona');
        }

        $nombreJuez = 'Un Juez';
        if ($this->evaluacion->usuarioBloqueo && $this->evaluacion->usuarioBloqueo->persona) {
            $nombreJuez = $this->evaluacion->usuarioBloqueo->persona->nombre . ' ' . $this->evaluacion->usuarioBloqueo->persona->apellido;
        }

        return [
            'id_evaluacion' => $this->evaluacion->id_evaluacion,
            'id_competidor' => $this->evaluacion->id_competidor,
            'id_examen'     => $this->evaluacion->id_examen,
            'bloqueado_por' => $this->evaluacion->bloqueado_por,
            'nombre_juez'   => $nombreJuez,
            'fecha_bloqueo' => $this->evaluacion->fecha_bloqueo,
            'estado'        => 'BLOQUEADO',
            'mensaje'       => "El competidor está siendo evaluado por {$nombreJuez}."
        ];
    }
}
