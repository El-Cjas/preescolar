<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Contracts\View\View;

class AlumnoFichaController extends Controller
{
    /**
     * Muestra la planilla oficial de inscripción para imprimir o exportar a PDF.
     */
    public function __invoke(Alumno $alumno): View
    {
        $alumno->load(['persona', 'representantes.persona']);

        $representanteLegal = $alumno->representantes
            ->where('pivot.representante_legal', true)
            ->first() ?? $alumno->representantes->first();

        return view('alumnos.ficha', [
            'alumno' => $alumno,
            'representanteLegal' => $representanteLegal,
        ]);
    }
}
