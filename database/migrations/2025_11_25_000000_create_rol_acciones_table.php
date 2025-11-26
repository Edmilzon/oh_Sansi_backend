<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_accion', function (Blueprint $table) {
            $table->id('id_rol_accion');
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_accion');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_rol')->references('id_rol')->on('rol')->onDelete('cascade');
            $table->foreign('id_accion')->references('id_accion')->on('accion_sistema')->onDelete('cascade');

            // Evitar duplicados de la misma acción para el mismo rol
            $table->unique(['id_rol', 'id_accion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_accion');
    }
};