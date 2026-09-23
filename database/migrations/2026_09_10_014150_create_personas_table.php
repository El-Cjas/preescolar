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
        Schema::create('personas', function (Blueprint $table) {
            // clave primaria id_persona, autoincrementable
            $table->id();

            $table->enum('prefijo', ['V', 'E', 'J', 'G']);
            $table->string('cedula', 15);
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['M', 'F']);
            $table->text('direccion');
            $table->string('email', 100)->unique();
            $table->enum('codigo_tlf', ['0412', '0414', '0422', '0424', '0416', '0426']);
            $table->string('numero_tlf', 7);

            // agregar created_at and updated_at columns automaticamente
            $table->timestamps();

            // clave unica compuesta de (prefijo + cedula)
            $table->unique(['prefijo', 'cedula']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
