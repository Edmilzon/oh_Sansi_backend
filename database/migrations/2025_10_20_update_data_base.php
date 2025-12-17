<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration{

    private array $tablas = [
        'descalificacion_administrativa',
        'medallero',
        'log_cambio_nota',
        'evaluacion',
        'examen',
        'evaluador_an',
        'grupo_competidor',
        'competidor',
        'competencia',
        'param_medallero',
        'parametro',
        'area_nivel_grado',
        'responsable_area',
        'area_nivel',
        'configuracion_accion',
        'cronograma_fase',
        'rol_accion',
        'usuario_rol',
        'area_olimpiada',
        'fase_global',
        'olimpiada',
        'usuario',
        'rol',
        'persona',
        'nivel',
        'institucion',
        'grupo',
        'grado_escolaridad',
        'departamento',
        'area',
        'archivo_csv',
        'accion_sistema',
        'personal_access_tokens',
        'migrations',
    ];

    public function up(): void{
        $this->down();

        Schema::create('migrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('migration');
            $table->integer('batch');
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('accion_sistema', function (Blueprint $table) {
            $table->id('id_accion_sistema');
            $table->string('codigo', 100)->unique();
            $table->string('nombre', 250);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('archivo_csv', function (Blueprint $table) {
            $table->id('id_archivo_csv');
            $table->string('nombre', 250);
            $table->date('fecha');
            $table->timestamps();
        });

        Schema::create('area', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre', 120);
            $table->timestamps();
        });

        Schema::create('departamento', function (Blueprint $table) {
            $table->id('id_departamento');
            $table->string('nombre', 20);
            $table->timestamps();
        });

        Schema::create('grado_escolaridad', function (Blueprint $table) {
            $table->id('id_grado_escolaridad');
            $table->text('nombre');
            $table->timestamps();
        });

        Schema::create('grupo', function (Blueprint $table) {
            $table->id('id_grupo');
            $table->string('nombre', 250);
            $table->timestamps();
        });

        Schema::create('institucion', function (Blueprint $table) {
            $table->id('id_institucion');
            $table->string('nombre', 250);
            $table->timestamps();
        });

        Schema::create('nivel', function (Blueprint $table) {
            $table->id('id_nivel');
            $table->string('nombre', 100);
            $table->timestamps();
        });

        Schema::create('persona', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('nombre');
            $table->string('apellido');
            $table->char('ci', 15)->unique();
            $table->char('telefono', 15);
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('rol', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre', 60);
            $table->timestamps();
        });

        Schema::create('olimpiada', function (Blueprint $table) {
            $table->id('id_olimpiada');
            $table->string('nombre', 100)->nullable();
            $table->char('gestion', 10);
            $table->tinyInteger('estado');
            $table->timestamps();
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();

            $table->foreign('id_persona')->references('id_persona')->on('persona');
        });

        Schema::create('fase_global', function (Blueprint $table) {
            $table->id('id_fase_global');
            $table->unsignedBigInteger('id_olimpiada')->nullable();
            $table->string('codigo', 25);
            $table->string('nombre', 50);
            $table->unsignedInteger('orden');
            $table->timestamps();

            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada')
                    ->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('area_olimpiada', function (Blueprint $table) {
            $table->id('id_area_olimpiada');
            $table->unsignedBigInteger('id_area')->nullable();
            $table->unsignedBigInteger('id_olimpiada')->nullable();
            $table->timestamps();

            $table->foreign('id_area')->references('id_area')->on('area');
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada');
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id('id_usuario_rol');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->unsignedBigInteger('id_olimpiada')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $table->foreign('id_rol')->references('id_rol')->on('rol');
            $table->foreign('id_olimpiada')->references('id_olimpiada')->on('olimpiada');
        });

        Schema::create('rol_accion', function (Blueprint $table) {
            $table->id('id_rol_accion');
            $table->unsignedBigInteger('id_rol')->nullable();
            $table->unsignedBigInteger('id_accion_sistema')->nullable();
            $table->integer('activo')->nullable();
            $table->timestamps();

            $table->foreign('id_rol')->references('id_rol')->on('rol');
            $table->foreign('id_accion_sistema')->references('id_accion_sistema')->on('accion_sistema');
        });

        Schema::create('cronograma_fase', function (Blueprint $table) {
            $table->id('id_cronograma_fase');
            $table->unsignedBigInteger('id_fase_global')->nullable();
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->tinyInteger('estado')->nullable();
            $table->timestamps();

            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global');
        });

        Schema::create('configuracion_accion', function (Blueprint $table) {
            $table->id('id_configuracion_accion');
            $table->unsignedBigInteger('id_accion_sistema')->nullable();
            $table->unsignedBigInteger('id_fase_global')->nullable();
            $table->tinyInteger('habilitada');
            $table->timestamps();

            $table->foreign('id_accion_sistema')->references('id_accion_sistema')->on('accion_sistema');
            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global');
        });

        Schema::create('area_nivel', function (Blueprint $table) {
            $table->id('id_area_nivel');
            $table->unsignedBigInteger('id_area_olimpiada')->nullable();
            $table->unsignedBigInteger('id_nivel')->nullable();
            $table->tinyInteger('es_activo')->nullable();
            $table->timestamps();

            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada');
            $table->foreign('id_nivel')->references('id_nivel')->on('nivel');
        });

        Schema::create('responsable_area', function (Blueprint $table) {
            $table->id('id_responsable_area');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_area_olimpiada')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $table->foreign('id_area_olimpiada')->references('id_area_olimpiada')->on('area_olimpiada');
        });

        Schema::create('area_nivel_grado', function (Blueprint $table) {
            $table->unsignedBigInteger('id_area_nivel');
            $table->unsignedBigInteger('id_grado_escolaridad');
            $table->primary(['id_area_nivel', 'id_grado_escolaridad']);

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel');
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad');
        });

        Schema::create('parametro', function (Blueprint $table) {
            $table->id('id_parametro');
            $table->unsignedBigInteger('id_area_nivel')->nullable();
            $table->decimal('nota_min_aprobacion', 8, 2)->nullable();
            $table->integer('cantidad_maxima')->nullable();
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel');
        });

        Schema::create('param_medallero', function (Blueprint $table) {
            $table->id('id_param_medallero');
            $table->unsignedBigInteger('id_area_nivel')->nullable();
            $table->integer('oro')->nullable();
            $table->integer('plata')->nullable();
            $table->integer('bronce')->nullable();
            $table->integer('mencion')->nullable();
            $table->timestamps();

            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel');
        });

        Schema::create('competencia', function (Blueprint $table) {
            $table->id('id_competencia');
            $table->unsignedBigInteger('id_fase_global')->nullable();
            $table->unsignedBigInteger('id_area_nivel')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado_fase', ['borrador', 'publicada', 'en_proceso', 'concluida', 'avalada'])
                    ->default('borrador');
            $table->enum('criterio_clasificacion', ['suma_ponderada', 'promedio_simple', 'manual'])
                    ->default('suma_ponderada');
            $table->unsignedBigInteger('id_usuario_aval')->nullable();
            $table->datetime('fecha_aval')->nullable();
            $table->timestamps();

            $table->foreign('id_fase_global')->references('id_fase_global')->on('fase_global');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel');
            $table->foreign('id_usuario_aval')->references('id_usuario')->on('usuario');
        });

        Schema::create('competidor', function (Blueprint $table) {
            $table->id('id_competidor');
            $table->unsignedBigInteger('id_archivo_csv')->nullable();
            $table->unsignedBigInteger('id_institucion')->nullable();
            $table->unsignedBigInteger('id_departamento')->nullable();
            $table->unsignedBigInteger('id_area_nivel')->nullable();
            $table->unsignedBigInteger('id_persona')->nullable();
            $table->unsignedBigInteger('id_grado_escolaridad')->nullable();
            $table->char('contacto_tutor', 15)->nullable();
            $table->char('genero', 2)->nullable();
            $table->string('estado_evaluacion')->default('disponible');
            $table->timestamps();

            $table->foreign('id_archivo_csv')->references('id_archivo_csv')->on('archivo_csv');
            $table->foreign('id_institucion')->references('id_institucion')->on('institucion');
            $table->foreign('id_departamento')->references('id_departamento')->on('departamento');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel');
            $table->foreign('id_persona')->references('id_persona')->on('persona');
            $table->foreign('id_grado_escolaridad')->references('id_grado_escolaridad')->on('grado_escolaridad');

            $table->index('estado_evaluacion');
        });

        Schema::create('grupo_competidor', function (Blueprint $table) {
            $table->id('id_grupo_competidor');
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->unsignedBigInteger('id_competidor')->nullable();
            $table->timestamps();

            $table->foreign('id_grupo')->references('id_grupo')->on('grupo');
            $table->foreign('id_competidor')->references('id_competidor')->on('competidor');
        });

        Schema::create('evaluador_an', function (Blueprint $table) {
            $table->id('id_evaluador_an');
            $table->unsignedBigInteger('id_usuario')->nullable();
            $table->unsignedBigInteger('id_area_nivel')->nullable();
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();

            $table->foreign('id_usuario')->references('id_usuario')->on('usuario');
            $table->foreign('id_area_nivel')->references('id_area_nivel')->on('area_nivel');
        });

        Schema::create('examen', function (Blueprint $table) {
            $table->id('id_examen');
            $table->unsignedBigInteger('id_competencia')->nullable();
            $table->string('nombre', 255);
            $table->decimal('ponderacion', 8, 2);
            $table->decimal('maxima_nota', 8, 2);
            $table->dateTime('fecha_hora_inicio')->nullable();
            $table->enum('tipo_regla', ['nota_corte'])->nullable();
            $table->longText('configuracion_reglas')->nullable();
            $table->enum('estado_ejecucion', ['no_iniciada', 'en_curso', 'finalizada'])
                    ->default('no_iniciada');
            $table->dateTime('fecha_inicio_real')->nullable();
            $table->timestamps();

            $table->foreign('id_competencia')->references('id_competencia')->on('competencia');
        });

        Schema::create('evaluacion', function (Blueprint $table) {
            $table->id('id_evaluacion');
            $table->unsignedBigInteger('id_competidor')->nullable();
            $table->unsignedBigInteger('id_examen')->nullable();
            $table->decimal('nota', 8, 2)->default(0);
            $table->enum('estado_participacion', ['presente', 'ausente', 'descalificado_etica'])
                    ->default('presente');
            $table->text('observacion')->nullable();
            $table->string('resultado_calculado', 50)->nullable();
            $table->unsignedBigInteger('bloqueado_por')->nullable();
            $table->dateTime('fecha_bloqueo')->nullable();
            $table->boolean('esta_calificado')->default(false);
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor');
            $table->foreign('id_examen')->references('id_examen')->on('examen');
            $table->foreign('bloqueado_por')->references('id_usuario')->on('usuario');

            $table->unique(['id_competidor', 'id_examen'], 'unique_evaluacion_competidor_examen');
        });

        Schema::create('medallero', function (Blueprint $table) {
            $table->id('id_medallero');
            $table->unsignedBigInteger('id_competidor')->nullable();
            $table->unsignedBigInteger('id_competencia')->nullable();
            $table->integer('puesto');
            $table->string('medalla', 15);
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor');
            $table->foreign('id_competencia')->references('id_competencia')->on('competencia');
        });

        Schema::create('log_cambio_nota', function (Blueprint $table) {
            $table->id('id_log_cambio_nota');
            $table->unsignedBigInteger('id_evaluacion')->nullable();
            $table->unsignedBigInteger('id_usuario_autor');
            $table->decimal('nota_nueva', 8, 2);
            $table->decimal('nota_anterior', 8, 2);
            $table->text('motivo_cambio');
            $table->timestamp('fecha_cambio')->useCurrent();

            $table->foreign('id_evaluacion')->references('id_evaluacion')->on('evaluacion');
            $table->foreign('id_usuario_autor')->references('id_usuario')->on('usuario');
        });

        Schema::create('descalificacion_administrativa', function (Blueprint $table) {
            $table->id('id_descalificacion');
            $table->unsignedBigInteger('id_competidor');
            $table->text('observaciones');
            $table->timestamp('fecha_descalificacion')->useCurrent();
            $table->timestamps();

            $table->foreign('id_competidor')->references('id_competidor')->on('competidor')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ($this->tablas as $tabla) {
            Schema::dropIfExists($tabla);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
