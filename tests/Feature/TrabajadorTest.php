<?php

use App\Livewire\Trabajadores\Index as TrabajadoresIndex;
use App\Models\Cargo;
use App\Models\Persona;
use App\Models\User;
use Livewire\Livewire;

test('el componente de trabajadores carga y puede buscar', function () {
    $user = User::factory()->create();
    $cargo = Cargo::create([
        'nombre_cargo' => 'Docente Titular',
        'descripcion' => 'Maestra de aula preescolar',
    ]);

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '17999888',
        'nombre' => 'Elena',
        'apellido' => 'Morales',
        'fecha_nacimiento' => '1992-04-15',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '8889900',
        'direccion' => 'Valencia Centro',
        'email' => 'elena.morales@example.com',
    ]);

    $trabajador = $persona->trabajador()->create([
        'fecha_ingreso' => '2022-09-15',
        'nivel_academico' => 'Universitario',
        'estatus_laboral' => 'Activo',
    ]);

    $trabajador->cargos()->attach($cargo->id, [
        'fecha_inicio' => '2022-09-15',
        'fecha_fin' => null,
    ]);

    Livewire::actingAs($user)
        ->test(TrabajadoresIndex::class)
        ->assertOk()
        ->assertSee('Elena')
        ->assertSee('Docente Titular')
        ->assertSee('Activo')
        ->set('search', 'Elena')
        ->assertSee('Elena')
        ->set('search', 'Inexistente')
        ->assertDontSee('Elena');
});

test('el filtro por estatus laboral funciona correctamente', function () {
    $user = User::factory()->create();

    $personaActiva = Persona::create([
        'prefijo' => 'V',
        'cedula' => '20111222',
        'nombre' => 'Carmen',
        'apellido' => 'Sosa',
        'fecha_nacimiento' => '1995-02-10',
        'genero' => 'F',
        'codigo_tlf' => '0412',
        'numero_tlf' => '1112233',
        'direccion' => 'Naguanagua',
        'email' => 'carmen.sosa@example.com',
    ]);
    $personaActiva->trabajador()->create([
        'fecha_ingreso' => '2023-01-10',
        'nivel_academico' => 'Universitario',
        'estatus_laboral' => 'Activo',
    ]);

    $personaInactiva = Persona::create([
        'prefijo' => 'V',
        'cedula' => '20333444',
        'nombre' => 'Pedro',
        'apellido' => 'Rangel',
        'fecha_nacimiento' => '1990-08-20',
        'genero' => 'M',
        'codigo_tlf' => '0416',
        'numero_tlf' => '4445566',
        'direccion' => 'San Diego',
        'email' => 'pedro.rangel@example.com',
    ]);
    $personaInactiva->trabajador()->create([
        'fecha_ingreso' => '2021-03-01',
        'nivel_academico' => 'Bachiller',
        'estatus_laboral' => 'Inactivo',
    ]);

    Livewire::actingAs($user)
        ->test(TrabajadoresIndex::class)
        ->set('estatusFilter', 'Activo')
        ->assertSee('Carmen')
        ->assertDontSee('Pedro')
        ->set('estatusFilter', 'Inactivo')
        ->assertSee('Pedro')
        ->assertDontSee('Carmen');
});

test('se puede registrar un nuevo trabajador con su cargo asignado', function () {
    $user = User::factory()->create();
    $cargo = Cargo::create([
        'nombre_cargo' => 'Auxiliar de Preescolar',
        'descripcion' => 'Apoyo en aula de clases',
    ]);

    Livewire::actingAs($user)
        ->test(TrabajadoresIndex::class)
        ->set('prefijo', 'V')
        ->set('cedula', '18333444')
        ->set('nombre', 'Luisa')
        ->set('apellido', 'Hernández')
        ->set('fecha_nacimiento', '1994-06-25')
        ->set('genero', 'F')
        ->set('email', 'luisa.hernandez@example.com')
        ->set('codigo_tlf', '0424')
        ->set('numero_tlf', '7778899')
        ->set('direccion', 'Guacara')
        ->set('fecha_ingreso', '2024-02-01')
        ->set('nivel_academico', 'Universitario')
        ->set('estatus_laboral', 'Activo')
        ->set('cargo_id', $cargo->id)
        ->set('fecha_inicio_cargo', '2024-02-01')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('personas', [
        'cedula' => '18333444',
        'nombre' => 'Luisa',
        'email' => 'luisa.hernandez@example.com',
    ]);

    $this->assertDatabaseHas('trabajadores', [
        'nivel_academico' => 'Universitario',
        'estatus_laboral' => 'Activo',
    ]);

    $this->assertDatabaseHas('trabajador_cargo', [
        'cargo_id' => $cargo->id,
        'fecha_inicio' => '2024-02-01',
    ]);
});

test('se puede actualizar un trabajador y cambiar su estatus laboral', function () {
    $user = User::factory()->create();
    $cargo = Cargo::create([
        'nombre_cargo' => 'Coordinadora Académica',
        'descripcion' => 'Coordinación pedagógica',
    ]);

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '15888777',
        'nombre' => 'Beatriz',
        'apellido' => 'Suárez',
        'fecha_nacimiento' => '1988-11-12',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '3332211',
        'direccion' => 'Los Guayos',
        'email' => 'beatriz.suarez@example.com',
    ]);

    $trabajador = $persona->trabajador()->create([
        'fecha_ingreso' => '2020-10-01',
        'nivel_academico' => 'Universitario',
        'estatus_laboral' => 'Activo',
    ]);

    $trabajador->cargos()->attach($cargo->id, [
        'fecha_inicio' => '2020-10-01',
        'fecha_fin' => null,
    ]);

    Livewire::actingAs($user)
        ->test(TrabajadoresIndex::class)
        ->call('editTrabajador', $trabajador->id)
        ->set('estatus_laboral', 'Reposo')
        ->set('direccion', 'Nueva dirección en Valencia')
        ->call('update')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('trabajadores', [
        'id' => $trabajador->id,
        'estatus_laboral' => 'Reposo',
    ]);

    $this->assertDatabaseHas('personas', [
        'id' => $persona->id,
        'direccion' => 'Nueva dirección en Valencia',
    ]);
});

test('se puede eliminar un trabajador y su registro se borra en cascada', function () {
    $user = User::factory()->create();

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '19555444',
        'nombre' => 'Javier',
        'apellido' => 'Romero',
        'fecha_nacimiento' => '1991-03-05',
        'genero' => 'M',
        'codigo_tlf' => '0426',
        'numero_tlf' => '5556677',
        'direccion' => 'Tocuyito',
        'email' => 'javier.romero@example.com',
    ]);

    $trabajador = $persona->trabajador()->create([
        'fecha_ingreso' => '2023-05-15',
        'nivel_academico' => 'Bachiller',
        'estatus_laboral' => 'Inactivo',
    ]);

    Livewire::actingAs($user)
        ->test(TrabajadoresIndex::class)
        ->call('confirmDelete', $trabajador->id)
        ->call('delete')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('trabajadores', [
        'id' => $trabajador->id,
    ]);

    $this->assertDatabaseMissing('personas', [
        'id' => $persona->id,
    ]);
});
