<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cronograma_fase', function (Blueprint $table) {
            $table->id('id_cronograma');
            
            // Relaciones con tus tablas existentes
            $table->unsignedBigInteger('id_olimpiada');
            $table->unsignedBigInteger('id_fase_global');
            
            // Datos nuevos que te faltaban
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->enum('estado', ['Pendiente', 'En Curso', 'Finalizada'])->default('Pendiente');
            
            $table->timestamps();

            // Claves foráneas (Sin tocar las tablas padre)
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')->onDelete('cascade');
            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global')->onDelete('cascade');

            // Candado de integridad: Una fase global solo aparece una vez por gestión
            $table->unique(['id_olimpiada', 'id_fase_global'], 'unique_crono_gestion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cronograma_fase');
    }
};