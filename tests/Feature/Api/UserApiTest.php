<?php

use App\Http\Middleware\ACLMiddleware;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
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

describe('Validations', function () {

    test('must validate as required when creating a new user with wempty fields.', function () {

        postJson(route('users.store'), [
            'name' => '',
            'email' => '',
            'password' => ''
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.required', ['attribute' => 'name']),
            'email' => trans('validation.required', ['attribute' => 'email']),
            'password' => trans('validation.required', ['attribute' => 'password'])
        ]);
    });

    test('must validate the minimum number of characters when creating a new user.', function () {

        postJson(route('users.store'), [
            'name' => 'Na',
            'email' => '1@2.com',
            'password' => '33'
        ], [ 'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.min.string', ['attribute' => 'name', 'min' => 3]),
            'email' => trans('validation.min.string', ['attribute' => 'email', 'min' => 8]),
            'password' => trans('validation.min.string', ['attribute' => 'password', 'min' => 6])
        ]);
    });

    test('must validate the maximum number of characters when creating a new user.', function () {

        postJson(route('users.store'), [
            'name' => \Illuminate\Support\Str::random(256),
            'email' => \Illuminate\Support\Str::random(256) . '@gmail.com',
            'password' => \Illuminate\Support\Str::random(21)
        ], [ 'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.max.string', ['attribute' => 'name', 'max' => 255]),
            'email' => trans('validation.max.string', ['attribute' => 'email', 'max' => 255]),
            'password' => trans('validation.max.string', ['attribute' => 'password', 'max' => 20])
        ]);
    });

    test('must validate the email format when creating a new user.', function () {

        postJson(route('users.store'), [
            'name' => 'Nalyar Ulryck',
            'email' => 'nalyarulryck',
            'password' => '123456'
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'email' => trans('validation.email', ['attribute' => 'email'])
        ]);
    });

    test('must validate the email uniqueness when creating a new user.', function () {

        postJson(route('users.store'), [
            'name' => 'Nalyar Ulryck',
            'email' => $this->user->email,
            'password' => '123456'
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'email' => trans('validation.unique', ['attribute' => 'email'])
        ]);
    });

    test('must validate as required when updating a user.', function () {

        putJson(route('users.update', $this->user->id), [
            'name' => '',
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.required', ['attribute' => 'name']),
        ]);
    });
    test('must validate the minimum number of characters when updating a user.', function () {

        putJson(route('users.update', $this->user->id), [
            'name' => 'Na',
            'password' => '123'
        ], [
            'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.min.string', ['attribute' => 'name', 'min' => 3]),
            'password' => trans('validation.min.string', ['attribute' => 'password', 'min' => 6])
        ]);
    });
    test('must validate the maximum number of characters when updating a user.', function () {

        putJson(route('users.update', $this->user->id), [
            'name' => \Illuminate\Support\Str::random(256),
            'password' => \Illuminate\Support\Str::random(21)
        ], [ 'Authorization' => 'Bearer ' . $this->token
        ])->assertStatus(422)->assertJsonValidationErrors([
            'name' => trans('validation.max.string', ['attribute' => 'name', 'max' => 255]),
            'password' => trans('validation.max.string', ['attribute' => 'password', 'max' => 20])
        ]);
    });

});

test('should return user details.', function () {

    $user = \App\Models\User::factory()->create();
    $reponse = getJson(route('users.show', $user->id), [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(200)->assertJsonStructure([
        'data' => [
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
    ]);
    // Posso fazer dessa forma também para validar a estrutura do json:
    // expect($reponse['data']['id'])->toBe($user->id);
    // expect($reponse['data']['name'])->toBe($user->name);
    // expect($reponse['data']['email'])->toBe($user->email);
    // expect($reponse['data']['permissions'])->toHaveCount(5);
});

test('should return user not found.', function () {

    getJson(route('users.show', 9999), [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(404)->assertJson([
        'message' => 'user not found'
    ]);
});

test('should update user information.', function () {

    $reponse = putJson(route('users.update', $this->user->id), [
        'name' => 'Nalyar Ulryck',
        'password' => '123456'
    ], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(200);
    assertDatabaseHas('users', [
        'id' => $this->user->id,
        'name' => 'Nalyar Ulryck',
        'password' => \Hash::check('123456', $this->user->password)
    ]);
});

test('should update user information without changing the password.', function () {

    $reponse = putJson(route('users.update', $this->user->id), [
        'name' => 'Nalyar Ulryck',
    ], [ 'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(200);
    assertDatabaseHas('users', [
        'id' => $this->user->id,
        'name' => 'Nalyar Ulryck',
    ]);
});

test('not should update user information.', function () {

    putJson(route('users.update', 9999), [
        'name' => 'Nalyar Ulryck',
        'password' => '123456'
    ], [ 'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(404)->assertJson([
        'message' => 'user not found'
    ]);
});

test('should delete a user.', function () {

    $user = \App\Models\User::factory()->create();
    deleteJson(route('users.destroy', $user->id), [], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(204);
    assertDatabaseMissing('users', [
        'id' => $user->id
    ]);

    // Funciona da mesma forma que o de cima, porém, ele verifica se o usuário foi deletado suavemente.
    // assertSoftDeleted('users', [
    //     'id' => $user->id
    // ]);
});

test('should not delete a user.', function () {

    deleteJson(route('users.destroy', 9999), [], [
        'Authorization' => 'Bearer ' . $this->token
    ])->assertStatus(404)->assertJson([
        'message' => 'user not found'
    ]);
});
