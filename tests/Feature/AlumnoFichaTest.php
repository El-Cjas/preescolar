<?php

use App\Models\Persona;
use App\Models\User;

test('los invitados no pueden ver la ficha oficial y son redirigidos al login', function () {
    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '10614289354',
        'nombre' => 'Mateo',
        'apellido' => 'Pérez',
        'fecha_nacimiento' => '2021-08-10',
        'genero' => 'M',
        'codigo_tlf' => '0414',
        'numero_tlf' => '1112233',
        'direccion' => 'Valencia',
        'email' => 'mateo@example.com',
    ]);
    $alumno = $persona->alumno()->create([
        'fecha_ingreso' => '2026-09-01',
        'lugar_nacimiento' => 'Valencia',
        'tipo_sangre' => 'O+',
    ]);

    $response = $this->get(route('alumnos.ficha', $alumno->id));
    $response->assertRedirect(route('login'));
});

test('un usuario autenticado puede ver la planilla oficial de inscripción con datos del alumno y representante', function () {
    $user = User::factory()->create();

    // 1. Alumno
    $personaAlumno = Persona::create([
        'prefijo' => 'V',
        'cedula' => '10614289354',
        'nombre' => 'Sofía',
        'apellido' => 'Hernández',
        'fecha_nacimiento' => '2021-04-12',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '2223344',
        'direccion' => 'Urb. El Parral, Casa 45',
        'email' => 'sofia@example.com',
    ]);
    $alumno = $personaAlumno->alumno()->create([
        'fecha_ingreso' => '2026-09-01',
        'lugar_nacimiento' => 'Valencia',
        'tipo_sangre' => 'A+',
        'peso' => 18.5,
        'estatura' => 1.05,
        'talla_camisa' => '6',
        'talla_pantalon' => '6',
    ]);

    // 2. Representante
    $personaMama = Persona::create([
        'prefijo' => 'V',
        'cedula' => '14289354',
        'nombre' => 'Gabriela',
        'apellido' => 'Rivas',
        'fecha_nacimiento' => '1987-11-20',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '5556677',
        'direccion' => 'Urb. El Parral, Casa 45',
        'email' => 'gabriela@example.com',
    ]);
    $representante = $personaMama->representante()->create([
        'ocupacion' => 'Contadora Pública',
    ]);

    // 3. Vinculación legal
    $alumno->representantes()->attach($representante->id, [
        'parentesco' => 'Madre',
        'representante_legal' => true,
    ]);

    // 4. Verificación de la vista
    $response = $this->actingAs($user)->get(route('alumnos.ficha', $alumno->id));

    $response->assertOk();
    $response->assertSee('Planilla Oficial de Inscripción Escolar');
    $response->assertSee('Sofía');
    $response->assertSee('Hernández');
    $response->assertSee('10614289354'); // Cédula Escolar
    $response->assertSee('A+'); // Tipo de sangre
    $response->assertSee('Gabriela'); // Mamá
    $response->assertSee('Contadora Pública'); // Ocupación
    $response->assertSee('Firma del Representante Legal');
});
