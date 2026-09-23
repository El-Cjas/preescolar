<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlumnoResource;
use App\Models\Alumno;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AlumnoController extends Controller
{
    /**
     * Devuelve el listado de alumnos (con soporte opcional para búsqueda).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $alumnos = Alumno::with(['persona', 'representantes.persona'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('persona', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('cedula', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->get();

        return AlumnoResource::collection($alumnos);
    }

    /**
     * Devuelve el detalle completo de un alumno específico.
     */
    public function show(int $id): AlumnoResource
    {
        $alumno = Alumno::with(['persona', 'representantes.persona'])->findOrFail($id);

        return new AlumnoResource($alumno);
    }
}
