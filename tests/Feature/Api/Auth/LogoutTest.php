<?php

use function Pest\Laravel\postJson;

it('should logout user', function () {
    $user = \App\Models\User::factory()->create();
    $data = [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'e2e-test'
    ];
    $response = postJson(route('auth.login'), $data);
    $token = $response->json('token');
    $response = postJson(route('auth.logout'), [], [
        'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(204);
});

it('should not logout user without token', function () {
    $response = postJson(route('auth.logout'), [], []);
    $response->assertJson(['message' => 'Unauthenticated.'])->assertStatus(401);
});


