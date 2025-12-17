<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id_usuario === (int) $id;
});

// CANAL EXAMEN
// Escuchado por: CompetidorBloqueado, CompetidorLiberado, ExamenEstadoCambiado
Broadcast::channel('examen.{id}', function ($user, $id) {
    // Aquí podrías validar: $user->esJuezDelExamen($id)
    // Por ahora, si está logueado, entra.
    return $user !== null;
});

// CANAL COMPETENCIA
// Escuchado por: ExamenEstadoCambiado (para habilitar botón), CompetenciaFinalizada
Broadcast::channel('competencia.{id}', function ($user, $id) {
    // Si está logueado, puede ver el estado de la competencia
    return $user !== null;
});

// Canal público para el estado global del sistema
Broadcast::channel('sistema-global', function () {
    return true; // Cualquiera puede escuchar
});
