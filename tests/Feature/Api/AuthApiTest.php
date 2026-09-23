<?php

use App\Models\User;

test('el login de la api rechaza credenciales incorrectas con 401', function () {
    $user = User::factory()->create([
        'email' => 'docente@preescolar.test',
        'password' => 'secreto123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'docente@preescolar.test',
        'password' => 'clave-erronea',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Las credenciales proporcionadas son incorrectas.',
        ]);
});

test('el login de la api genera un bearer token con credenciales validas', function () {
    $user = User::factory()->create([
        'email' => 'directora@preescolar.test',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'directora@preescolar.test',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'token',
            'user' => ['id', 'name', 'email'],
        ]);

    $token = $response->json('token');
    expect($token)->not->toBeEmpty();

    // Verificamos que con el token podemos consultar /api/me
    $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/me');

    $meResponse->assertOk()
        ->assertJson([
            'id' => $user->id,
            'email' => 'directora@preescolar.test',
        ]);
});

test('el logout de la api revoca el token actual', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout');

    $response->assertOk()
        ->assertJson([
            'message' => 'Sesión cerrada exitosamente.',
        ]);

    // Comprobamos que el token ya no existe en la base de datos
    expect($user->tokens()->count())->toBe(0);
});
