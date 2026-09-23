<?php

use App\Livewire\Representantes\Index as RepresentantesIndex;
use App\Models\Persona;
use App\Models\User;
use Livewire\Livewire;

test('el componente de representantes carga y puede buscar', function () {
    $user = User::factory()->create();

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '15111222',
        'nombre' => 'Mariana',
        'apellido' => 'Castillo',
        'fecha_nacimiento' => '1989-05-20',
        'genero' => 'F',
        'codigo_tlf' => '0414',
        'numero_tlf' => '3334455',
        'direccion' => 'Valencia',
        'email' => 'mariana@example.com',
    ]);
    $persona->representante()->create([
        'ocupacion' => 'Arquitecta',
    ]);

    Livewire::actingAs($user)
        ->test(RepresentantesIndex::class)
        ->assertOk()
        ->assertSee('Mariana')
        ->assertSee('Arquitecta')
        ->set('search', 'Mariana')
        ->assertSee('Mariana')
        ->set('search', 'Inexistente')
        ->assertDontSee('Mariana');
});

test('se puede registrar un nuevo representante mediante el formulario livewire', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(RepresentantesIndex::class)
        ->set('prefijo', 'V')
        ->set('cedula', '19888999')
        ->set('nombre', 'Carlos')
        ->set('apellido', 'Mendoza')
        ->set('fecha_nacimiento', '1985-11-15')
        ->set('genero', 'M')
        ->set('ocupacion', 'Contador Público')
        ->set('email', 'carlos.mendoza@example.com')
        ->set('codigo_tlf', '0412')
        ->set('numero_tlf', '7654321')
        ->set('direccion', 'Urb. Trigal Norte, Calle 3')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('personas', [
        'cedula' => '19888999',
        'nombre' => 'Carlos',
        'email' => 'carlos.mendoza@example.com',
    ]);

    $this->assertDatabaseHas('representantes', [
        'ocupacion' => 'Contador Público',
    ]);
});

test('se puede actualizar un representante existente', function () {
    $user = User::factory()->create();

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '16222333',
        'nombre' => 'Patricia',
        'apellido' => 'Díaz',
        'fecha_nacimiento' => '1990-03-10',
        'genero' => 'F',
        'codigo_tlf' => '0416',
        'numero_tlf' => '1234567',
        'direccion' => 'Naguanagua',
        'email' => 'patricia@example.com',
    ]);
    $representante = $persona->representante()->create([
        'ocupacion' => 'Abogada',
    ]);

    Livewire::actingAs($user)
        ->test(RepresentantesIndex::class)
        ->call('editRepresentante', $representante->id)
        ->set('ocupacion', 'Jueza Superior')
        ->set('direccion', 'Naguanagua, Calle Principal Casa 12')
        ->call('update')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('representantes', [
        'id' => $representante->id,
        'ocupacion' => 'Jueza Superior',
    ]);

    $this->assertDatabaseHas('personas', [
        'id' => $persona->id,
        'direccion' => 'Naguanagua, Calle Principal Casa 12',
    ]);
});

test('se puede eliminar un representante y su persona', function () {
    $user = User::factory()->create();

    $persona = Persona::create([
        'prefijo' => 'V',
        'cedula' => '17444555',
        'nombre' => 'Roberto',
        'apellido' => 'Pérez',
        'fecha_nacimiento' => '1987-08-14',
        'genero' => 'M',
        'codigo_tlf' => '0424',
        'numero_tlf' => '9876543',
        'direccion' => 'San Diego',
        'email' => 'roberto@example.com',
    ]);
    $representante = $persona->representante()->create([
        'ocupacion' => 'Mecánico',
    ]);

    Livewire::actingAs($user)
        ->test(RepresentantesIndex::class)
        ->call('confirmDelete', $representante->id)
        ->call('delete')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('representantes', [
        'id' => $representante->id,
    ]);

    $this->assertDatabaseMissing('personas', [
        'id' => $persona->id,
    ]);
});
