<?php

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('unauthenticated user should not get me', function () {
    $response = getJson(route('auth.me'), []);
    $response->assertJson(['message' => 'Unauthenticated.'])->assertStatus(401);
});

it('should return user with permissions', function () {
    $user = \App\Models\User::factory()->create();
    $permission = \App\Models\Permission::factory()->count(10)->create()->pluck('id')->toArray();
    $user->permissions()->attach($permission);
    $data = [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'e2e-test'
    ];
    $response = postJson(route('auth.login'), $data);
    $token = $response->json('token');
    $response = getJson(route('auth.me'), [
        'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(200)->assertJsonStructure(['data' => [
        'id',
        'name',
        'email',
        'permissions' => [
            '*' => [
                'id',
                'name',
                'description',
                ]
            ]
        ]
    ])->assertJsonCount(10, 'data.permissions');
});


