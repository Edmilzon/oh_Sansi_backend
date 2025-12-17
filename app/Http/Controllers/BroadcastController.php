<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Usuario;
use App\Model\Examen;
use App\Model\EvaluadorAn;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Exception;

class BroadcastController extends Controller
{
    /**
     * Autentica manualmente las conexiones a canales privados de Reverb/Pusher.
     * Sin usar auth:sanctum.
     */
    public function authenticate(Request $request): JsonResponse
    {
        // 1. Validar Inputs del Handshake
        $request->validate([
            'socket_id' => 'required|string',
            'channel_name' => 'required|string',
            'user_id' => 'required|integer|exists:usuario,id_usuario' // Identidad Explícita
        ]);

        $socketId = $request->input('socket_id');
        $channelName = $request->input('channel_name');
        $userId = $request->input('user_id');

        // 2. Lógica de Autorización por Canal
        if ($this->autorizarCanal($channelName, $userId)) {
            return $this->generarRespuestaPusher($socketId, $channelName);
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }

    /**
     * Router interno de autorización.
     */
    private function autorizarCanal(string $channelName, int $userId): bool
    {
        // 1. Canal de Examen (Sala de Evaluación)
        if (str_starts_with($channelName, 'private-examen.')) {
            $idExamen = (int) str_replace('private-examen.', '', $channelName);
            return $this->esJuezDelExamen($userId, $idExamen);
        }

        // 2. Canal de Usuario (Notificaciones)
        if (str_starts_with($channelName, 'private-usuario.')) {
            $targetUserId = (int) str_replace('private-usuario.', '', $channelName);
            return $userId === $targetUserId;
        }

        // 3. NUEVO: Canal de Competencia (Para la Lista de Exámenes en tiempo real)
        // Formato: "private-competencia.{id}"
        if (str_starts_with($channelName, 'private-competencia.')) {
            // Aquí puedes aplicar lógica de negocio:
            // ¿Es Juez de algún examen de esta competencia?
            // ¿Es Responsable de área?
            // Por simplicidad para tu demo, permitimos si el usuario existe (autenticado)
            return Usuario::where('id_usuario', $userId)->exists();
        }

        return false;
    }

    /**
     * Regla de Negocio: ¿Este usuario es Juez activo del Área/Nivel del examen?
     */
    private function esJuezDelExamen(int $userId, int $idExamen): bool
    {
        $examen = Examen::with('competencia')->find($idExamen);

        if (!$examen) return false;

        $idAreaNivel = $examen->competencia->id_area_nivel;

        // Verificar en la tabla pivote de asignaciones
        return EvaluadorAn::where('id_usuario', $userId)
            ->where('id_area_nivel', $idAreaNivel)
            ->where('estado', 1) // Debe estar activo
            ->exists();
    }

    /**
     * Genera la firma HMAC SHA256 requerida por el protocolo Pusher/Reverb.
     */
    private function generarRespuestaPusher(string $socketId, string $channelName): JsonResponse
    {
        $appKey = Config::get('broadcasting.connections.reverb.key'); // O 'pusher.key'
        $appSecret = Config::get('broadcasting.connections.reverb.secret'); // O 'pusher.secret'

        $stringToSign = $socketId . ':' . $channelName;
        $signature = hash_hmac('sha256', $stringToSign, $appSecret);

        return response()->json([
            'auth' => $appKey . ':' . $signature
        ]);
    }
}
