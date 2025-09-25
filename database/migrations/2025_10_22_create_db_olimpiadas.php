<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('persona', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('nombre');
            $table->string('apellido');
            $table->string('ci')->unique();
            $table->date('fecha_nac');
            $table->enum('genero', ['M', 'F'])->nullable();
            $table->string('telefono')->nullable()->unique();
            $table->string('email')->unique();
            $table->timestamps();
        });
        
        Schema::create('area', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('nivel', function (Blueprint $table) {
            $table->id('id_nivel');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->integer('orden')->nullable();// recordar que hace
            $table->timestamps();
        });


        Schema::create('institucion', function (Blueprint $table) {
            $table->id('id_institucion');
            $table->string('nombre');
            $table->string('tipo')->nullable();
            $table->string('departamento');
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable()->unique(); // buscar mas info al respecto
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona')->onDelete('set null');
        });

        // Tablas que dependen de las anteriores

        Schema::create('codigo_evaluador', function (Blueprint $table) {
            $table->id('id_codigo_evaluador');
            $table->string('codigo')->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_area')->nullable();
            $table->unsignedBigInteger('id_nivel')->nullable();
            $table->timestamps();

            $table->foreign('id_area')->references('id_area')->on('area')->onDelete('cascade');
            $table->foreign('id_nivel')->references('id_nivel')->on('nivel')->onDelete('set null');
        });

        Schema::create('codigo_encargado', function (Blueprint $table) {
            $table->id('id_codigo_encargado');
            $table->string('codigo')->unique();
            $table->string('descripcion')->nullable();
            $table->unsignedBigInteger('id_area');
            $table->timestamps();

            $table->foreign('id_area')->references('id_area')->on('area')->onDelete('cascade');
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombre');
            $table->string('password_hash');
            $table->enum('rol', ['privilegiado', 'competidor', 'evaluador', 'responsable_area']);
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_codigo_evaluador')->nullable();
            $table->unsignedBigInteger('id_codigo_encargado')->nullable();
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona')->onDelete('cascade');
            $table->foreign('id_codigo_evaluador')->references('id_codigo_evaluador')->on('codigo_evaluador')->onDelete('set null');
            $table->foreign('id_codigo_encargado')->references('id_codigo_encargado')->on('codigo_encargado')->onDelete('set null');
        });

        Schema::create('competidor', function (Blueprint $table) {
            $table->id('id_competidor');
            $table->string('grado_escolar');
            $table->string('departamento');
            $table->string('contacto_tutor')->nullable();
            $table->string('contacto_emergencia')->nullable();
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_institucion');
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona')->onDelete('cascade');
            $table->foreign('id_institucion')->references('id_institucion')->on('institucion')->onDelete('cascade');
        });

        Schema::create('competencia', function (Blueprint $table) {
            $table->id('id_competencia');
            $table->integer('anio');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado');
            $table->unsignedBigInteger('id_area');
            $table->unsignedBigInteger('id_nivel');
            $table->timestamps();

            $table->foreign('id_area')->references('id_area')->on('area')->onDelete('cascade');
            $table->foreign('id_nivel')->references('id_nivel')->on('nivel')->onDelete('cascade');
        });

        Schema::create('parametro', function (Blueprint $table) {
            $table->id('id_parametro');
            $table->decimal('nota_min_clasif');
            $table->integer('max_oros')->default(0);
            $table->integer('max_platas')->default(0);
            $table->integer('max_bronces')->default(0);
            $table->integer('max_menciones')->default(0);
            $table->unsignedBigInteger('id_competencia');
            $table->timestamps();

            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')->onDelete('cascade');
        });

        Schema::create('inscripcion', function (Blueprint $table) {
            $table->id('id_inscripcion');
            $table->date('fecha_inscripcion');
            $table->string('estado');
            $table->unsignedBigInteger('id_competencia');
            $table->unsignedBigInteger('id_competidor');
            $table->timestamps();

            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')->onDelete('cascade');
            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
        });

        Schema::create('fase', function (Blueprint $table) {
            $table->id('id_fase');
            $table->string('nombre');
            $table->integer('orden');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluador', function (Blueprint $table) {
            $table->id('id_evaluador');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_persona');
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona')->onDelete('cascade');
        });

        Schema::create('evaluador_area', function (Blueprint $table) {
            $table->id('id_evaluador_area');
            $table->unsignedBigInteger('id_evaluador');
            $table->unsignedBigInteger('id_area');
            $table->timestamps();

            $table->foreign('id_evaluador')->references('id_evaluador')->on('evaluador')->onDelete('cascade');
            $table->foreign('id_area')->references('id_area')->on('area')->onDelete('cascade');
        });

        Schema::create('evaluacion', function (Blueprint $table) {
            $table->id('id_evaluacion');
            $table->decimal('nota');
            $table->text('observaciones')->nullable();
            $table->date('fecha_evaluacion');
            $table->string('estado');
            $table->unsignedBigInteger('id_fase');
            $table->unsignedBigInteger('id_evaluador');
            $table->unsignedBigInteger('id_competidor');
            $table->timestamps();

            $table->foreign('id_fase')->references('id_fase')->on('fase')->onDelete('cascade');
            $table->foreign('id_evaluador')->references('id_evaluador')->on('evaluador')->onDelete('cascade');
            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
        });

        Schema::create('historial_evaluacion', function (Blueprint $table) {
            $table->id('id_historial');
            $table->dateTime('fecha_cambio');
            $table->decimal('nota_antigua');
            $table->decimal('nota_nueva');
            $table->text('motivo_cambio')->nullable();
            $table->unsignedBigInteger('id_evaluacion');
            $table->timestamps();

            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluacion')->onDelete('cascade');
        });

        Schema::create('desclasificacion', function (Blueprint $table) {
            $table->id('id_desclasificacion');
            $table->date('fecha');
            $table->text('motivo');
            $table->unsignedBigInteger('id_competidor');
            $table->unsignedBigInteger('id_evaluacion')->nullable();
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluacion')->onDelete('set null');
        });

        Schema::create('responsable_area', function (Blueprint $table) {
            $table->id('id_responsable_area');
            $table->date('fecha_asignacion');
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_area');
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona')->onDelete('cascade');
            $table->foreign('id_area')->references('id_area')->on('area')->onDelete('cascade');
        });

        Schema::create('aval', function (Blueprint $table) {
            $table->id('id_aval');
            $table->date('fecha_aval');
            $table->string('estado');
            $table->unsignedBigInteger('id_competencia');
            $table->unsignedBigInteger('id_fase');
            $table->unsignedBigInteger('id_responsable_area');
            $table->timestamps();

            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')->onDelete('cascade');
            $table->foreign('id_fase')->references('id_fase')->on('fase')->onDelete('cascade');
            $table->foreign('id_responsable_area')->references('id_responsable_area')->on('responsable_area')->onDelete('cascade');
        });

        Schema::create('grupo', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('max_integrantes')->nullable();
            $table->timestamps();
        });

        Schema::create('grupo_competidor', function (Blueprint $table) {
            $table->id('id_grupo_competidor');
            $table->unsignedBigInteger('id_grupo');
            $table->unsignedBigInteger('id_competidor');
            $table->timestamps();

            $table->foreign('id_grupo')->references('id_grupo')->on('grupo')->onDelete('cascade');
            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
        });

        Schema::create('medallero', function (Blueprint $table) {
            $table->id('id_medallero');
            $table->integer('puesto');
            $table->enum('medalla', ['oro', 'plata', 'bronce', 'mencion'])->nullable();
            $table->unsignedBigInteger('id_competidor');
            $table->unsignedBigInteger('id_competencia');
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('persona');
        Schema::dropIfExists('institucion');
        Schema::dropIfExists('competidor');
        Schema::dropIfExists('area');
        Schema::dropIfExists('nivel');
        Schema::dropIfExists('competencia');
        Schema::dropIfExists('parametro');
        Schema::dropIfExists('inscripcion');
        Schema::dropIfExists('fase');
        Schema::dropIfExists('evaluador');
        Schema::dropIfExists('evaluador_area');
        Schema::dropIfExists('evaluacion');
        Schema::dropIfExists('historial_evaluacion');
        Schema::dropIfExists('desclasificacion');
        Schema::dropIfExists('responsable_area');
        Schema::dropIfExists('aval');
        Schema::dropIfExists('grupo');
        Schema::dropIfExists('grupo_competidor');
        Schema::dropIfExists('medallero');
        Schema::dropIfExists('codigo_evaluador');
        Schema::dropIfExists('codigo_encargado');
        Schema::dropIfExists('codigo_acceso');
    }
};