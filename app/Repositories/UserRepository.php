<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function __construct(protected User $user) {}

    public function getPaginate(int $totalPerPage = 15,int $page = 1, string $filter = ''): LengthAwarePaginator
    {
        return $this->user->where(function ($query) use ($filter) {
            if ($filter !== '') {
                $query->where('name', 'like', "%{$filter}%");
            }
        })->paginate($totalPerPage, ['*'], 'page', $page);
        /**
         * Estou passando a consulta($query) junto com o filtro ($filter) para a função de callback;
         * essa função primeiro valida se o $filter atende o if e depois adiciona o where que de fato será aglutinado à consultado
         */
    }

    public function createNew(string $name, string $email, string $password) : User {
        return $this->user->create([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);
    }
}

/**
 * É uma extensão do model de usuários;
 * serve apenas para deixar a model limpa;
 * traz toda as querys(consultas no banco) pra cá.
 */
