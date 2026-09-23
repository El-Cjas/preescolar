<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();

            // relacion 1:1 con persona
            $table->foreignId('persona_id')->unique()->constrained('personas')->cascadeOnDelete();

            $table->date('fecha_ingreso');
            $table->enum('nivel_academico', ['Primaria', 'Bachiller', 'Universitario']);
            $table->enum('estatus_laboral', ['Activo', 'Inactivo', 'Reposo'])->default('Activo');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajadors');
    }
};
