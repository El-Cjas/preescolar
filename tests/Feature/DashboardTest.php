<?php

use App\Livewire\Dashboard;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('el componente livewire del dashboard muestra metricas y enlaces', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertOk()
        ->assertSee('Panel de Control Directivo')
        ->assertSee('Matrícula Escolar')
        ->assertSee('Representantes')
        ->assertSee('Personal / Nómina');
});
