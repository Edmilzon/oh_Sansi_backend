<?php

namespace App\Events;

use App\Model\Evaluacion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CompetidorLiberado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Evaluacion
     */
    public $evaluacion;

    /**
     * @var float|null
     */
    public $nuevaNota;

    /**
     * Create a new event instance.
     *
     * @param Evaluacion $evaluacion
     * @param float|null $nuevaNota (Opcional) La nota actualizada si hubo guardado.
     */
    public function __construct(Evaluacion $evaluacion, ?float $nuevaNota = null)
    {
        $this->evaluacion = $evaluacion;
        $this->nuevaNota = $nuevaNota;
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
        return 'CompetidorLiberado';
    }

    /**
     * Get the data to broadcast.
     * Payload que recibe el Frontend.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        // Nota: Si $this->nuevaNota es null, usamos la nota actual del modelo.
        // Si es una descalificación (nota 0 forzada), $nuevaNota vendrá como 0.
        $notaFinal = $this->nuevaNota ?? $this->evaluacion->nota;

        return [
            'id_evaluacion' => $this->evaluacion->id_evaluacion,
            'id_competidor' => $this->evaluacion->id_competidor,
            'id_examen'     => $this->evaluacion->id_examen,
            'nueva_nota'    => $notaFinal,
            'esta_calificado' => $this->evaluacion->esta_calificado,
            'estado_participacion' => $this->evaluacion->estado_participacion,
            'resultado_calculado' => $this->evaluacion->resultado_calculado,
            'estado'        => 'LIBRE',
            'mensaje'       => 'Ficha liberada y actualizada.'
        ];
    }
}
