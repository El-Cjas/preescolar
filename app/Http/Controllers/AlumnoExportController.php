<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlumnoExportController extends Controller
{
    /**
     * Exporta la matrícula escolar en formato CSV compatible con Microsoft Excel.
     */
    public function __invoke(): StreamedResponse
    {
        $fecha = now()->format('Y-m-d');
        $fileName = "censo_matricula_preescolar_{$fecha}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 para que Excel reconozca tildes y caracteres en español
            fwrite($handle, "\xEF\xBB\xBF");

            // Encabezados de columnas
            fputcsv($handle, [
                'Cédula Escolar',
                'Nombres',
                'Apellidos',
                'Fecha Nacimiento',
                'Edad (Años)',
                'Género',
                'Lugar Nacimiento',
                'Tipo de Sangre',
                'Peso (kg)',
                'Estatura (m)',
                'Talla Camisa',
                'Talla Pantalón',
                'Dirección Habitación',
                'Fecha Ingreso',
                'Representante Legal',
                'Cédula Representante',
                'Parentesco',
                'Teléfono Representante',
                'Correo Representante',
                'Ocupación Representante',
            ], ';');

            // Recorrer alumnos en bloques para eficiencia
            Alumno::with(['persona', 'representantes.persona'])
                ->chunk(100, function ($alumnos) use ($handle) {
                    foreach ($alumnos as $alumno) {
                        $repLegal = $alumno->representantes
                            ->where('pivot.representante_legal', true)
                            ->first() ?? $alumno->representantes->first();

                        fputcsv($handle, [
                            $alumno->persona->prefijo.'-'.$alumno->persona->cedula,
                            $alumno->persona->nombre,
                            $alumno->persona->apellido,
                            $alumno->persona->fecha_nacimiento->format('d/m/Y'),
                            $alumno->persona->fecha_nacimiento->age,
                            $alumno->persona->genero === 'M' ? 'Masculino' : 'Femenino',
                            $alumno->lugar_nacimiento,
                            $alumno->tipo_sangre,
                            $alumno->peso ?? '',
                            $alumno->estatura ?? '',
                            $alumno->talla_camisa ?? '',
                            $alumno->talla_pantalon ?? '',
                            $alumno->persona->direccion,
                            $alumno->fecha_ingreso->format('d/m/Y'),
                            $repLegal ? $repLegal->persona->nombre.' '.$repLegal->persona->apellido : 'Sin representante',
                            $repLegal ? $repLegal->persona->prefijo.'-'.$repLegal->persona->cedula : '',
                            $repLegal ? $repLegal->pivot->parentesco : '',
                            $repLegal ? $repLegal->persona->codigo_tlf.'-'.$repLegal->persona->numero_tlf : '',
                            $repLegal ? $repLegal->persona->email : '',
                            $repLegal ? $repLegal->ocupacion : '',
                        ], ';');
                    }
                });

            fclose($handle);
        }, $fileName, $headers);
    }
}
