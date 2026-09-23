<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlumnoResource extends JsonResource
{
    /**
     * Transforma el recurso Alumno en un array JSON optimizado para la app móvil.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $repLegal = $this->representantes
            ? $this->representantes->where('pivot.representante_legal', true)->first() ?? $this->representantes->first()
            : null;

        return [
            'id' => $this->id,
            'nombre' => $this->persona->nombre,
            'apellido' => $this->persona->apellido,
            'nombre_completo' => "{$this->persona->nombre} {$this->persona->apellido}",
            'cedula_escolar' => "{$this->persona->prefijo}-{$this->persona->cedula}",
            'genero' => $this->persona->genero,
            'edad' => $this->persona->fecha_nacimiento->age,
            'fecha_nacimiento' => $this->persona->fecha_nacimiento->format('d/m/Y'),
            'lugar_nacimiento' => $this->lugar_nacimiento,
            'tipo_sangre' => $this->tipo_sangre,
            'peso' => $this->peso,
            'estatura' => $this->estatura,
            'talla_camisa' => $this->talla_camisa,
            'talla_pantalon' => $this->talla_pantalon,
            'direccion' => $this->persona->direccion,
            'fecha_ingreso' => $this->fecha_ingreso->format('d/m/Y'),
            'representante' => $repLegal ? [
                'nombre_completo' => "{$repLegal->persona->nombre} {$repLegal->persona->apellido}",
                'cedula' => "{$repLegal->persona->prefijo}-{$repLegal->persona->cedula}",
                'parentesco' => $repLegal->pivot->parentesco,
                'telefono' => "{$repLegal->persona->codigo_tlf}-{$repLegal->persona->numero_tlf}",
                'email' => $repLegal->persona->email,
                'ocupacion' => $repLegal->ocupacion,
            ] : null,
        ];
    }
}
