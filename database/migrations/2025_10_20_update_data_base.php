<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('niveles', function (Blueprint $table) {
            $table->id('id_nivel');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('olimpiadas', function (Blueprint $table) {
            $table->id('id_olimpiada');
            $table->string('nombre');
            $table->string('gestion');
            $table->timestamps();
        });

        Schema::create('area_nivel', function (Blueprint $table) {
            $table->id('id_area_nivel');
            $table->unsignedBigInteger('id_area');
            $table->unsignedBigInteger('id_nivel');
            $table->unsignedBigInteger('id_olimpiada');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_area')->references('id_area')->on('areas')->onDelete('cascade');
            $table->foreign('id_nivel')->references('id_nivel')->on('niveles')->onDelete('cascade');
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiadas')->onDelete('cascade');
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('ci')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('telefono')->nullable();
            $table->unsignedBigInteger('id_olimpiada');
            $table->timestamps();

            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiadas')->onDelete('cascade');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id('id_usuario_rol');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->unsignedBigInteger('id_olimpiada');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_rol')->references('id_rol')->on('roles')->onDelete('cascade');
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiadas')->onDelete('cascade');
        });

        Schema::create('instituciones', function (Blueprint $table) {
            $table->id('id_institucion');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('parametros', function (Blueprint $table) {
            $table->id('id_parametro');
            $table->integer('nota_max_clasif');
            $table->integer('nota_min_clasif');
            $table->integer('cantidad_max_apro');
            $table->unsignedBigInteger('id_area_nivel');
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('fases', function (Blueprint $table) {
            $table->id('id_fase');
            $table->string('nombre');
            $table->integer('orden')->default(1);
            $table->unsignedBigInteger('id_area_nivel');
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('responsable_area', function (Blueprint $table) {
            $table->id('id_responsableArea');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area_nivel');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('evaluador_an', function (Blueprint $table) {
            $table->id('id_evaluadorAN');
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_area_nivel');
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
        });

        Schema::create('archivos_csv', function (Blueprint $table) {
            $table->id('id_archivo_csv');
            $table->string('nombre');
            $table->date('fecha');
            $table->unsignedBigInteger('id_olimpiada');
            $table->timestamps();

            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiadas')->onDelete('cascade');
        });

        Schema::create('competidores', function (Blueprint $table) {
            $table->id('id_competidor');
            $table->string('datos');
            $table->unsignedBigInteger('id_institucion');
            $table->unsignedBigInteger('id_area_nivel');
            $table->unsignedBigInteger('id_archivo_csv')->nullable();
            $table->timestamps();

            $table->foreign('id_institucion')->references('id_institucion')->on('instituciones')->onDelete('cascade');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel')->onDelete('cascade');
            $table->foreign('id_archivo_csv')->references('id_archivo_csv')->on('archivos_csv')->onDelete('set null');
        });

        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id('id_evaluacion');
            $table->decimal('nota', 5, 2);
            $table->text('observaciones')->nullable();
            $table->date('fecha_evaluacion');
            $table->string('estado')->default('pendiente');
            $table->unsignedBigInteger('id_evaluadorAN')->nullable();
            $table->unsignedBigInteger('id_competidor');
            $table->timestamps();

            $table->foreign('id_evaluadorAN')->references('id_evaluadorAN')->on('evaluador_an')->onDelete('set null');
            $table->foreign('id_competidor')->references('id_competidor')->on('competidores')->onDelete('cascade');
        });
        
        Schema::create('competencias', function (Blueprint $table) {
            $table->id('id_competencia');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado');
            $table->unsignedBigInteger('id_responsableArea')->nullable();
            $table->unsignedBigInteger('id_evaluadorAN')->nullable();
            $table->unsignedBigInteger('id_competidor')->nullable();
            $table->unsignedBigInteger('id_fase')->nullable();
            $table->unsignedBigInteger('id_parametro')->nullable();
            $table->unsignedBigInteger('id_evaluacion')->nullable();
            $table->timestamps();

            $table->foreign('id_responsableArea')->references('id_responsableArea')->on('responsable_area')->onDelete('set null');
            $table->foreign('id_evaluadorAN')->references('id_evaluadorAN')->on('evaluador_an')->onDelete('set null');
            $table->foreign('id_competidor')->references('id_competidor')->on('competidores')->onDelete('set null');
            $table->foreign('id_fase')->references('id_fase')->on('fases')->onDelete('set null');
            $table->foreign('id_parametro')->references('id_parametro')->on('parametros')->onDelete('set null');
            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluaciones')->onDelete('set null');
        });

        Schema::create('grupos', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('nombre');
            $table->unsignedBigInteger('id_fase');
            $table->timestamps();

            $table->foreign('id_fase')->references('id_fase')->on('fases')->onDelete('cascade');
        });

        Schema::create('grupos_competidores', function (Blueprint $table) {
            $table->id('id_grupo_competidor');
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_competidor');
            $table->timestamps();

            $table->foreign('id_grupo')->references('id_grupo')->on('grupos')->onDelete('cascade');
            $table->foreign('id_competidor')->references('id_competidor')->on('competidores')->onDelete('cascade');
        });

        Schema::create('aval', function (Blueprint $table) {
            $table->id('id_aval');
            $table->date('fecha_aval');
            $table->string('estado');
            $table->unsignedBigInteger('id_competencia');
            $table->unsignedBigInteger('id_fase');
            $table->unsignedBigInteger('id_responsableArea');
            $table->timestamps();

            $table->foreign('id_competencia')->references('id_competencia')->on('competencias')->onDelete('cascade');
            $table->foreign('id_fase')->references('id_fase')->on('fases')->onDelete('cascade');
            $table->foreign('id_responsableArea')->references('id_responsableArea')->on('responsable_area')->onDelete('cascade');
        });

        Schema::create('desclasificaciones', function (Blueprint $table) {
            $table->id('id_desclasificacion');
            $table->date('fecha');
            $table->text('motivo');
            $table->unsignedBigInteger('id_competidor');
            $table->unsignedBigInteger('id_evaluacion')->nullable();
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidores')->onDelete('cascade');
            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluaciones')->onDelete('set null');
        });

        Schema::create('medallero', function (Blueprint $table) {
            $table->id('id_medallero');
            $table->integer('puesto');
            $table->string('medalla');
            $table->unsignedBigInteger('id_competidor');
            $table->unsignedBigInteger('id_competencia');
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidores')->onDelete('cascade');
            $table->foreign('id_competencia')->references('id_competencia')->on('competencias')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medallero');
        Schema::dropIfExists('desclasificaciones');
        Schema::dropIfExists('aval');
        Schema::dropIfExists('grupos_competidores');
        Schema::dropIfExists('grupos');
        Schema::dropIfExists('evaluaciones');
        Schema::dropIfExists('competencias');
        Schema::dropIfExists('competidores');
        Schema::dropIfExists('archivos_csv');
        Schema::dropIfExists('evaluador_an');
        Schema::dropIfExists('responsable_area');
        Schema::dropIfExists('fases');
        Schema::dropIfExists('parametros');
        Schema::dropIfExists('instituciones');
        Schema::dropIfExists('usuario_rol');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('area_nivel');
        Schema::dropIfExists('olimpiadas');
        Schema::dropIfExists('niveles');
        Schema::dropIfExists('areas');
    }
};
