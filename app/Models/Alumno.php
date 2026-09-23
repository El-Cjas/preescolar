<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Override;

class Alumno extends Model
{
    use HasFactory;

    protected $fillable = [
        'persona_id',
        'fecha_ingreso',
        'lugar_nacimiento',
        'talla_camisa',
        'talla_pantalon',
        'peso',
        'estatura',
        'tipo_sangre',
    ];

    #[Override]
    protected function casts()
    {
        return [
            'fecha_ingreso' => 'date',
            'peso' => 'decimal:2',
            'estatura' => 'decimal:2',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function representantes(): BelongsToMany
    {
        return $this->belongsToMany(Representante::class, 'alumno_representante')
            ->withPivot('parentesco', 'representante_legal')
            ->withTimestamps();
    }
}
