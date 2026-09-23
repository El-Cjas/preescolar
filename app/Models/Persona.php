<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persona extends Model
{
    use HasFactory;

    protected $fillable = [
        'prefijo',
        'cedula',
        'nombre',
        'apellido',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'email',
        'codigo_tlf',
        'numero_tlf',
    ];

    // Convierte la fecha automáticamente a objeto Carbon (Fecha)
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
        ];
    }

    public function alumno(): HasOne
    {
        return $this->hasOne(Alumno::class);
    }

    // una perosona puede tener perfil de representante
    public function representante(): HasOne
    {
        return $this->hasOne(Representante::class);
    }

    public function trabajador(): HasOne
    {
        return $this->hasOne(Trabajador::class);
    }
}
