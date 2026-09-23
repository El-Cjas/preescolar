<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Trabajador extends Model
{
    use HasFactory;

    // Laravel en plural para "Trabajador" busca "trabajadors" en inglés.
    // Le indicamos que en español nuestra tabla es "trabajadores":
    protected $table = 'trabajadores';

    protected $fillable = [
        'persona_id',
        'fecha_ingreso',
        'nivel_academico',
        'estatus_laboral',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function cargos(): BelongsToMany
    {
        return $this->belongsToMany(Cargo::class, 'trabajador_cargo')
            ->withPivot('fecha_inicio', 'fecha_fin')
            ->withTimestamps();
    }
}
