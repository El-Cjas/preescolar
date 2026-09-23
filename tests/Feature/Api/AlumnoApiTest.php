<?php

use App\Models\Persona;
use App\Models\User;

test('la ruta de alumnos en la api requiere autenticacion con token', function () {
    $response = $this->getJson('/api/alumnos');
    $response->assertStatus(401);
});

test('un usuario con token puede obtener el listado de alumnos estructurado con AlumnoResource', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '10614289354',
        'nombre' => 'Camila',
        'apellido' => 'Navarro',
        'fecha_nacimiento' => '2021-05-18',
        'genero' => 'F',
        'codigo_tlf' => '0412',
        'numero_tlf' => '3332211',
        'direccion' => 'San Diego',
        'email' => 'camila@example.com',
    ]);
    $alumno = $persona->alumno()->create([
        'fecha_ingreso' => '2026-09-01',
        'lugar_nacimiento' => 'Valencia',
        'tipo_sangre' => 'A+',
        'peso' => 17.8,
        'estatura' => 1.02,
        'talla_camisa' => '4',
        'talla_pantalon' => '4',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/alumnos');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nombre',
                    'apellido',
                    'nombre_completo',
                    'cedula_escolar',
                    'genero',
                    'edad',
                    'fecha_nacimiento',
                    'tipo_sangre',
                    'peso',
                    'estatura',
                    'representante',
                ],
            ],
        ]);

    $response->assertJsonFragment([
        'nombre_completo' => 'Camila Navarro',
        'cedula_escolar' => 'V-10614289354',
        'tipo_sangre' => 'A+',
    ]);
});

test('se puede consultar el detalle de un alumno con su representante legal en la api', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $personaAlumno = Persona::create([
        'prefijo' => 'V',
        'cedula' => '10614289355',
        'nombre' => 'Lucas',
        'apellido' => 'García',
        'fecha_nacimiento' => '2021-02-14',
        'genero' => 'M',
        'codigo_tlf' => '0414',
        'numero_tlf' => '4445566',
        'direccion' => 'Guacara',
        'email' => 'lucas@example.com',
    ]);
    $alumno = $personaAlumno->alumno()->create([
        'fecha_ingreso' => '2026-09-01',
        'lugar_nacimiento' => 'Valencia',
        'tipo_sangre' => 'O+',
    ]);

    $personaMama = Persona::create([
        'prefijo' => 'V',
        'cedula' => '16555444',
        'nombre' => 'Elena',
        'apellido' => 'García',
        'fecha_nacimiento' => '1988-09-10',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '7778899',
        'direccion' => 'Guacara',
        'email' => 'elena.garcia@example.com',
    ]);
    $representante = $personaMama->representante()->create([
        'ocupacion' => 'Docente',
    ]);

    $alumno->representantes()->attach($representante->id, [
        'parentesco' => 'Madre',
        'representante_legal' => true,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/alumnos/{$alumno->id}");

    $response->assertOk()
        ->assertJsonFragment([
            'nombre_completo' => 'Lucas García',
            'cedula_escolar' => 'V-10614289355',
        ])
        ->assertJsonPath('data.representante.nombre_completo', 'Elena García')
        ->assertJsonPath('data.representante.parentesco', 'Madre')
        ->assertJsonPath('data.representante.ocupacion', 'Docente');
});

test('un usuario con token puede descargar el censo escolar en csv via api o con parametro query token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '10614289356',
        'nombre' => 'Andrés',
        'apellido' => 'Mendoza',
        'fecha_nacimiento' => '2021-06-20',
        'genero' => 'M',
        'codigo_tlf' => '0412',
        'numero_tlf' => '5551122',
        'direccion' => 'Valencia',
        'email' => 'andres@example.com',
    ]);
    $persona->alumno()->create([
        'fecha_ingreso' => '2026-09-01',
        'lugar_nacimiento' => 'Valencia',
        'tipo_sangre' => 'B+',
    ]);

    // 1. Probar descarga con Header Authorization Bearer
    $responseHeader = $this->withHeader('Authorization', "Bearer {$token}")
        ->get('/api/alumnos/exportar/csv');

    $responseHeader->assertOk();
    $responseHeader->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($responseHeader->streamedContent())->toContain('Andrés')
        ->and($responseHeader->streamedContent())->toContain('Mendoza');

    // 2. Probar descarga desde navegador móvil con ?token=...
    $responseQuery = $this->get("/api/alumnos/exportar/csv?token={$token}");
    $responseQuery->assertOk();
    $responseQuery->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($responseQuery->streamedContent())->toContain('Andrés');
});

test('un usuario con token puede acceder a la planilla oficial de inscripcion via api', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '10614289357',
        'nombre' => 'Valeria',
        'apellido' => 'Rojas',
        'fecha_nacimiento' => '2021-07-15',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '8889900',
        'direccion' => 'Naguanagua',
        'email' => 'valeria@example.com',
    ]);
    $alumno = $persona->alumno()->create([
        'fecha_ingreso' => '2026-09-01',
        'lugar_nacimiento' => 'Naguanagua',
        'tipo_sangre' => 'O-',
    ]);

    // Probar acceso con ?token=... como lo hace la app móvil al abrir el navegador
    $response = $this->get("/api/alumnos/{$alumno->id}/ficha?token={$token}");

    $response->assertOk();
    $response->assertSee('Planilla Oficial de Inscripción Escolar');
    $response->assertSee('Valeria');
    $response->assertSee('Rojas');
    $response->assertSee('10614289357');
});

test('los reportes en la api rechazan peticiones sin autenticacion', function () {
    $this->get('/api/alumnos/exportar/csv')->assertStatus(401);
    $this->get('/api/alumnos/1/ficha')->assertStatus(401);
});
