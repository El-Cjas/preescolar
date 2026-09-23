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
        Schema::create('alumno_representante', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('representante_id')->constrained('representantes')->cascadeOnDelete();

            // datos propios de la relacion
            $table->string('parentesco', 50);
            $table->boolean('representante_legal')->default(false);

            $table->timestamps();

            $table->unique(['alumno_id', 'representante_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumno_representante');
    }
};
