<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Model\AccionSistema;

class AccionSistemaSeeder extends Seeder
{
    public function run(): void
    {
        $catalogoOficial = [
            [
                'codigo'      => 'DASHBOARD',
                'nombre'      => 'Panel de Control',
                'descripcion' => 'Vista principal con métricas, accesos directos y estado general. Ruta: /dashboard',
            ],
            [
                'codigo'      => 'OLIMPIADAS',
                'nombre'      => 'Gestión de Olimpiadas',
                'descripcion' => 'Creación y selección de nuevas gestiones y eventos. Ruta: /olimpiadas',
            ],
            [
                'codigo'      => 'CRONOGRAMA',
                'nombre'      => 'Cronograma de Actividades',
                'descripcion' => 'Línea de tiempo para configurar fechas de inicio/fin. Ruta: /cronograma',
            ],
            [
                'codigo'      => 'FASES',
                'nombre'      => 'Configuración de Fases',
                'descripcion' => 'Administración de etapas (Distrital, Nacional) y reglas. Ruta: /fases',
            ],
            [
                'codigo'      => 'AREAS',
                'nombre'      => 'Áreas de Conocimiento',
                'descripcion' => 'Gestión de materias o áreas temáticas. Ruta: /areas',
            ],
            [
                'codigo'      => 'NIVELES',
                'nombre'      => 'Niveles y Grados',
                'descripcion' => 'Configuración de niveles de dificultad y grados escolares. Ruta: /niveles',
            ],
            [
                'codigo'      => 'ASIGNACIONES',
                'nombre'      => 'Asignación de Niveles',
                'descripcion' => 'Vinculación lógica entre Áreas y Niveles. Ruta: /asignaciones',
            ],
            [
                'codigo'      => 'INSCRIPCION',
                'nombre'      => 'Inscripción Masiva',
                'descripcion' => 'Importación masiva (CSV) de estudiantes y validación. Ruta: /inscritos',
            ],
            [
                'codigo'      => 'COMPETIDORES',
                'nombre'      => 'Lista de Competidores',
                'descripcion' => 'Base de datos consultable de estudiantes inscritos. Ruta: /competidores',
            ],
            [
                'codigo'      => 'RESPONSABLES',
                'nombre'      => 'Responsables de Área',
                'descripcion' => 'Registro y asignación de usuarios encargados de área. Ruta: /responsables',
            ],
            [
                'codigo'      => 'EVALUADORES',
                'nombre'      => 'Evaluadores',
                'descripcion' => 'Gestión de usuarios con rol de corrección. Ruta: /evaluadores',
            ],
            [
                'codigo'      => 'COMPETENCIAS',
                'nombre'      => 'Gestión de Competencias',
                'descripcion' => 'Monitor de instancias activas de competencia. Ruta: /competencias',
            ],
            [
                'codigo'      => 'EXAMENES',
                'nombre'      => 'Banco de Exámenes',
                'descripcion' => 'Subida, generación y configuración de pruebas. Ruta: /examenes',
            ],
            [
                'codigo'      => 'SALA_EVALUACION',
                'nombre'      => 'Sala de Evaluación',
                'descripcion' => 'Interfaz de calificación en tiempo real. Ruta: /evaluacion-sala',
            ],
            [
                'codigo'      => 'MEDALLERO',
                'nombre'      => 'Configuración de Medallas',
                'descripcion' => 'Parametrización de reglas para Oro, Plata y Bronce. Ruta: /medallero',
            ],
            [
                'codigo'      => 'REPORTES',
                'nombre'      => 'Reportes y Auditoría',
                'descripcion' => 'Historial de cambios, notas y resultados finales. Ruta: /reportes',
            ],
            [
                'codigo'      => 'PARAMETROS',
                'nombre'      => 'Configuración del Sistema',
                'descripcion' => 'Ajustes globales técnicos y administrativos. Ruta: /parametros',
            ],
            [
                'codigo'      => 'GESTIONAR_ROLES',
                'nombre'      => 'Gestión de Roles y Permisos',
                'descripcion' => 'Permiso crítico para asignar accesos.',
            ],
            [
                'codigo'      => 'CONFIGURAR_SISTEMA',
                'nombre'      => 'Configuración Global (Backend)',
                'descripcion' => 'Permiso de backend para endpoints de configuración.',
            ],
        ];

        $this->command->info('🏛️  Cargando Catálogo Oficial de Secciones...');

        foreach ($catalogoOficial as $data) {
            AccionSistema::firstOrCreate(
                ['codigo' => $data['codigo']],
                [
                    'nombre'      => $data['nombre'],
                    'descripcion' => $data['descripcion']
                ]
            );
        }

        $this->command->info('✅ Catálogo cargado correctamente.');
    }
}
