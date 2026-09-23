<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cargo extends Model
{
    protected $fillable = [
        'nombre_cargo',
        'descripcion',
    ];

    public function trabajadores(): BelongsToMany
    {
        return $this->belongsToMany(Trabajador::class, 'trabajador_cargo')
            ->withPivot('fecha_inicio', 'fecha_fin')
            ->withTimestamps();
    }
}
