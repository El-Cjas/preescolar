<?php

use App\Livewire\Alumnos\Index;
use App\Models\Alumno;
use App\Models\Persona;
use App\Models\User;
use Livewire\Livewire;

test('se puede crear una persona y conectarla con su ficha de alumno', function () {

    // 1. Creamos la persona
    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '14970041',
        'nombre' => 'Juan',
        'apellido' => 'Pérez',
        'fecha_nacimiento' => '1990-01-01',
        'genero' => 'M',
        'codigo_tlf' => '0414',
        'numero_tlf' => '1234567',
        'direccion' => 'Calle Falsa 123',
        'email' => 'juan.perez@example.com',
    ]);
    // 2. Le creamos el alumno mediante la relación
    $persona->alumno()->create([
        'fecha_ingreso' => '2023-01-01',
        'lugar_nacimiento' => 'Caracas',
        'tipo_sangre' => 'O+',
    ]);
    // 3. Comprobamos que realmente se guardó en la base de datos
    $this->assertDatabaseHas('personas', [
        'cedula' => '14970041',
        'nombre' => 'Juan',
    ]);
    $this->assertDatabaseHas('alumnos', [
        'persona_id' => $persona->id,
        'lugar_nacimiento' => 'Caracas',
    ]);

    // 4. Comprobamos la relación y que la edad calculada sea correcta
    $alumno = Alumno::first();

    expect($alumno->persona->nombre)->toBe('Juan')
        ->and($alumno->persona->fecha_nacimiento->age)->toBeGreaterThan(30)
        ->and($alumno->persona->fecha_nacimiento->format('d/m/Y'))->toBe('01/01/1990');

});

test('se puede vincular un alumno con su representante y datos pivote', function () {
    // 1. Creamos al niño (Alumno)
    $personaHijo = Persona::create([
        'prefijo' => 'V',
        'cedula' => '32111222',
        'nombre' => 'Carlitos',
        'apellido' => 'Gómez',
        'fecha_nacimiento' => '2021-06-10',
        'genero' => 'M',
        'codigo_tlf' => '0412',
        'numero_tlf' => '9998877',
        'direccion' => 'Urb. Los Samanes',
        'email' => 'carlitos@example.com',
    ]);
    $alumno = $personaHijo->alumno()->create([
        'fecha_ingreso' => '2026-09-01',
        'lugar_nacimiento' => 'Valencia',
        'tipo_sangre' => 'A+',
    ]);
    // 2. Creamos a la mamá (Representante)
    $personaMama = Persona::create([
        'prefijo' => 'V',
        'cedula' => '18555666',
        'nombre' => 'Ana',
        'apellido' => 'Martínez',
        'fecha_nacimiento' => '1988-03-20',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '5554433',
        'direccion' => 'Urb. Los Samanes',
        'email' => 'ana.martinez@example.com',
    ]);
    $representante = $personaMama->representante()->create([
        'ocupacion' => 'Ingeniera de Sistemas',
    ]);
    // 3. LA MAGIA: Los vinculamos con attach() y le pasamos los datos pivote
    $alumno->representantes()->attach($representante->id, [
        'parentesco' => 'Madre',
        'representante_legal' => true,
    ]);
    // 4. Verificamos que se guardó en la tabla pivote
    $this->assertDatabaseHas('alumno_representante', [
        'alumno_id' => $alumno->id,
        'representante_id' => $representante->id,
        'parentesco' => 'Madre',
        'representante_legal' => true,
    ]);
    // 5. Navegamos del Hijo hacia la Mamá (con sus datos pivote):
    expect($alumno->representantes)->toHaveCount(1)
        ->and($alumno->representantes->first()->persona->nombre)->toBe('Ana')
        ->and($alumno->representantes->first()->pivot->parentesco)->toBe('Madre')
        ->and($alumno->representantes->first()->pivot->representante_legal)->toBe(1);
    // 6. Y navegamos de la Mamá hacia el Hijo:
    expect($representante->alumnos)->toHaveCount(1)
        ->and($representante->alumnos->first()->persona->nombre)->toBe('Carlitos');
});

test('el componente de alumnos carga y puede buscar en tiempo real', function () {
    $user = User::factory()->create();

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '29888777',
        'nombre' => 'Valentina',
        'apellido' => 'Sánchez',
        'fecha_nacimiento' => '2021-04-12',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '1112233',
        'direccion' => 'Caracas',
        'email' => 'valentina@example.com',
    ]);
    $persona->alumno()->create([
        'fecha_ingreso' => '2026-09-01',
        'lugar_nacimiento' => 'Caracas',
        'tipo_sangre' => 'O+',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->assertOk()
        ->assertSee('Valentina')
        ->set('search', 'Valentina')
        ->assertSee('Valentina')
        ->set('search', 'Inexistente')
        ->assertDontSee('Valentina')
        ->assertSee('No se encontraron alumnos');
});
