<?php

use App\Models\Persona;
use App\Models\User;

test('los invitados no pueden exportar el censo y son redirigidos al login', function () {
    $response = $this->get(route('alumnos.export.csv'));
    $response->assertRedirect(route('login'));
});

test('un usuario autenticado puede descargar el censo escolar en formato csv con datos del alumno y representante', function () {
    $user = User::factory()->create();

    $personaAlumno = Persona::create([
        'prefijo' => 'V',
        'cedula' => '10614289354',
        'nombre' => 'Sebastián',
        'apellido' => 'Castillo',
        'fecha_nacimiento' => '2021-03-15',
        'genero' => 'M',
        'codigo_tlf' => '0414',
        'numero_tlf' => '1234567',
        'direccion' => 'Urb. La Viña, Calle 2',
        'email' => 'sebastian@example.com',
    ]);
    $alumno = $personaAlumno->alumno()->create([
        'fecha_ingreso' => '2026-09-01',
        'lugar_nacimiento' => 'Valencia',
        'tipo_sangre' => 'O+',
        'peso' => 19.2,
        'estatura' => 1.08,
        'talla_camisa' => '6',
        'talla_pantalon' => '6',
    ]);

    $personaMama = Persona::create([
        'prefijo' => 'V',
        'cedula' => '14289354',
        'nombre' => 'Verónica',
        'apellido' => 'Silva',
        'fecha_nacimiento' => '1989-07-22',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '9876543',
        'direccion' => 'Urb. La Viña, Calle 2',
        'email' => 'veronica@example.com',
    ]);
    $representante = $personaMama->representante()->create([
        'ocupacion' => 'Ingeniera Civil',
    ]);

    $alumno->representantes()->attach($representante->id, [
        'parentesco' => 'Madre',
        'representante_legal' => true,
    ]);

    $response = $this->actingAs($user)->get(route('alumnos.export.csv'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

    // Comprobar contenido del stream
    $content = $response->streamedContent();

    expect($content)->toContain('Cédula Escolar')
        ->and($content)->toContain('Nombres')
        ->and($content)->toContain('Apellidos')
        ->and($content)->toContain('V-10614289354')
        ->and($content)->toContain('Sebastián')
        ->and($content)->toContain('Castillo')
        ->and($content)->toContain('Verónica Silva')
        ->and($content)->toContain('Ingeniera Civil');
});
