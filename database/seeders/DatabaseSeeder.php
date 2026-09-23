<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\Cargo;
use App\Models\Persona;
use App\Models\Representante;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Director',
            'email' => 'admin@preescolar.test',
        ]);

        $cargos = ([
            Cargo::create(['nombre_cargo' => 'Directora', 'descripcion' => 'direccion general del plantel']),
            Cargo::create(['nombre_cargo' => 'Docente titular', 'descripcion' => 'maestra a cargo de la seccion']),
            Cargo::create(['nombre_cargo' => 'Auxiliar de preescolar', 'descripcion' => 'auxiliar en la enseñanza de preescolar']),
            Cargo::create(['nombre_cargo' => 'Personal Administrativo', 'descripcion' => 'Control de estudios y secretaría']),
        ]);

        // crear trabajadores con sus cargos(maestro y directora)
        foreach ($cargos as $cargo) {
            $personaTrabajador = Persona::factory()->create();
            $trabajador = $personaTrabajador->trabajador()->create([
                'fecha_ingreso' => fake()->date('Y-m-d', '- 3 years'),
                'nivel_academico' => 'Universitario',
                'estatus_laboral' => 'Activo',
            ]);

            // asignamo el cargo con fecha de inicio
            $trabajador->cargos()->attach($cargo->id, [
                'fecha_inicio' => '2023-09-15',
                'fecha_fin' => null,
            ]);
        }
        // 4. Crear 10 Familias (Representante + Alumno vinculado)
        for ($i = 0; $i < 10; $i++) {
            // creamos al papa o mama
            $personaMama = Persona::factory()->create();
            $representante = $personaMama->representante()->create([
                'ocupacion' => fake()->randomElement(['Ingeniero', 'Doctor', 'Abogado', 'Maestra', 'Otro']),
            ]);

            // creamos al niño (edad entre 3 y 6 años)
            $personaNino = Persona::factory()->create([
                'fecha_nacimiento' => fake()->dateTimeBetween('-6 years', '-3 years')->format('Y-m-d'),
            ]);
            $alumno = $personaNino->alumno()->create([
                'fecha_ingreso' => '2026-09-01',
                'lugar_nacimiento' => 'caracas',
                'talla_camisa' => fake()->randomElement(['4', '6', '8']),
                'talla_pantalon' => fake()->randomElement(['4', '6', '8']),
                'peso' => fake()->randomFloat(2, 15, 30),
                'estatura' => fake()->randomFloat(2, 0.90, 1.15),
                'tipo_sangre' => fake()->randomElement(['O+', 'A+', 'B+', 'AB+']),
            ]);

            // C) Vinculamos al alumno con su mamá en la tabla pivote
            $alumno->representantes()->attach($representante->id, [
                'parentesco' => $personaMama->genero === 'F' ? 'Madre' : 'Padre',
                'representante_legal' => true,
            ]);

        }

    }
}
