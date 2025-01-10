<?php

use App\Http\Middleware\ACLMiddleware;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutMiddleware;

// Caso eu queira desabilitar o middleware ACLMiddleware
// beforeEach(function () {
//     withoutMiddleware(ACLMiddleware::class);
// });


beforeEach(function () {
    $this->user = \App\Models\User::factory()->create();
    $this->token = $this->user->createToken('e2e-test')->plainTextToken;
    $permissions = [
        ['name' => 'users.index', 'description' => 'List all users'],
        ['name' => 'users.store', 'description' => 'Store a new user'],
        ['name' => 'users.show', 'description' => 'Show user details'],
        ['name' => 'users.update', 'description' => 'Update user information'],
        ['name' => 'users.destroy', 'description' => 'Delete a user']
    ];

    $this->permissions = collect($permissions)->map(function ($permission) {
        return \App\Models\Permission::factory()->create($permission)->id;
    })->flatten();

    $this->user->permissions()->attach($this->permissions);
});

test('should return all users.', function () {

    \App\Models\User::factory()->count(10)->create();
    $reponse = getJson(route('users.index'), [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(200)->assertJsonStructure([
        'data' => [
            '*' => [
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
        ],
        'meta' => [
            'current_page',
            'from',
            'last_page',
            'path',
            'per_page',
            'to',
            'total'
        ]
    ]);
    expect($reponse->json('data'))->toHaveCount(11);
    expect($reponse['meta']['total'])->toBe(11);
});

test('should return users with pagination.', function () {

    \App\Models\User::factory()->count(25)->create();
    $reponse = getJson(route('users.index') . '?page=2', [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(200)->assertJsonStructure([
        'data' => [
            '*' => [
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
        ],
        'meta' => [
            'current_page',
            'from',
            'last_page',
            'path',
            'per_page',
            'to',
            'total'
        ]
    ]);
    expect($reponse->json('data'))->toHaveCount(11);
    expect($reponse['meta']['total'])->toBe(26);
});

test('should increase the number of users per page.', function () {

    \App\Models\User::factory()->count(24)->create();
    $reponse = getJson(route('users.index') . '?total_per_page=6', [
        'Authorization' => 'Bearer ' . $this->token,
    ])->assertStatus(200)->assertJsonStructure([
        'data' => [
            '*' => [
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
        ],
        'meta' => [
            'current_page',
            'from',
            'last_page',
            'path',
            'per_page',
            'to',
            'total'
        ]
    ]);
    expect($reponse->json('data'))->toHaveCount(6);
    expect($reponse['meta']['total'])->toBe(25);
    expect($reponse['meta']['per_page'])->toBe(6);
});

test('should return users with filter.', function () {

    \App\Models\User::factory()->count(10)->create();
    \App\Models\User::factory()->count(10)->create([
        'name' => 'Nalyar Ulryck'
    ]);
    $reponse = getJson(route('users.index') . '?filter=nalyar', [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(200)->assertJsonStructure([
        'data' => [
            '*' => [
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
        ],
        'meta' => [
            'current_page',
            'from',
            'last_page',
            'path',
            'per_page',
            'to',
            'total'
        ]
    ]);
    expect($reponse->json('data'))->toHaveCount(10);
    expect($reponse['meta']['total'])->toBe(10);
});

test('should create a new user.', function () {

    $reponse = postJson(route('users.store'), [
        'name' => 'Nalyar Ulryck',
        'email' => 'raylanzeeroo@oulook.com',
        'password' => '123456'
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(201)->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'email'
        ]
    ]);
    assertDatabaseHas('users', [
        'id' => $reponse['data']['id'],
        'name' => $reponse['data']['name'],
        'email' => $reponse['data']['email']
    ]);
});
