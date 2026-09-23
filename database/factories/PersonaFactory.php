<?php

namespace Database\Factories;

use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Persona>
 */
class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prefijo' => fake()->randomElement(['V', 'E']),
            'cedula' => (string) fake()->unique()->numberBetween(10000000, 35000000),
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'fecha_nacimiento' => fake()->date('Y-m-d', '- 20 years'),
            'genero' => fake()->randomElement(['M', 'F']),
            'direccion' => fake()->address(),
            'email' => fake()->unique()->safeEmail(),
            'codigo_tlf' => fake()->randomElement(['0412', '0414', '0424', '0416', '0426']),
            'numero_tlf' => (string) fake()->numberBetween(1000000, 9999999),
        ];
    }
}
