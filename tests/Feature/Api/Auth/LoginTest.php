<?php

use function Pest\Laravel\postJson;

it('should auth user', function () {
    $user = \App\Models\User::factory()->create();
    $data = [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'e2e-test'
    ];
    postJson(route('auth.login'), $data)
    ->assertStatus(200)
    ->assertJsonStructure(['token']);
});

it('should not auth user with wrong password', function () {
    $user = \App\Models\User::factory()->create();
    $data = [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'e2e-test'
    ];
    postJson(route('auth.login'), $data)
    ->assertStatus(422)
    ->assertJsonStructure(['message']);
});

it('should not auth user with wrong email', function () {
    $user = \App\Models\User::factory()->create();
    $data = [
        'email' => 'fake@email.com',
        'password' => 'password',
        'device_name' => 'e2e-test'
    ];
    postJson(route('auth.login'), $data)
    ->assertStatus(422)
    ->assertJsonStructure(['message']);
});
