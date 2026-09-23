<?php

namespace App\Livewire;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\Representante;
use App\Models\Trabajador;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $totalAlumnos = Alumno::count();
        $totalNinos = Alumno::whereHas('persona', fn ($q) => $q->where('genero', 'M'))->count();
        $totalNinas = Alumno::whereHas('persona', fn ($q) => $q->where('genero', 'F'))->count();

        $totalRepresentantes = Representante::count();

        $totalTrabajadores = Trabajador::count();
        $docentesActivos = Trabajador::where('estatus_laboral', 'Activo')->count();
        $docentesReposo = Trabajador::where('estatus_laboral', 'Reposo')->count();
        $docentesInactivos = Trabajador::where('estatus_laboral', 'Inactivo')->count();

        $ratioDocente = $docentesActivos > 0
            ? round($totalAlumnos / $docentesActivos, 1).' : 1'
            : 'N/A';

        // Últimos 5 alumnos inscritos
        $ultimosAlumnos = Alumno::with(['persona', 'representantes.persona'])
            ->latest('id')
            ->take(5)
            ->get();

        // Cargos con conteo de personal actual
        $cargosConteo = Cargo::withCount(['trabajadores' => function ($query) {
            $query->wherePivotNull('fecha_fin');
        }])->get();

        // Personal en reposo (para alertas administrativas)
        $personalEnReposo = Trabajador::with(['persona', 'cargos'])
            ->where('estatus_laboral', 'Reposo')
            ->get();

        return view('livewire.dashboard', [
            'totalAlumnos' => $totalAlumnos,
            'totalNinos' => $totalNinos,
            'totalNinas' => $totalNinas,
            'totalRepresentantes' => $totalRepresentantes,
            'totalTrabajadores' => $totalTrabajadores,
            'docentesActivos' => $docentesActivos,
            'docentesReposo' => $docentesReposo,
            'docentesInactivos' => $docentesInactivos,
            'ratioDocente' => $ratioDocente,
            'ultimosAlumnos' => $ultimosAlumnos,
            'cargosConteo' => $cargosConteo,
            'personalEnReposo' => $personalEnReposo,
        ]);
    }
}
