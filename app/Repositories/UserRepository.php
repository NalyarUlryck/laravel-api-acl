<?php

namespace App\Repositories;

use App\DTO\Users\CreateUserDTO;
use App\DTO\Users\EditUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function __construct(protected User $user) {}

    public function getPaginate(int $totalPerPage, int $page, string $filter): LengthAwarePaginator
    {
        return $this->user->where(function ($query) use ($filter) {
            if ($filter !== '') {
                $query->where('name', 'like', "%{$filter}%");
            }
        })->with(['permissions'])
            ->paginate($totalPerPage, ['*'], 'page', $page);
        /**
         * Estou passando a consulta($query) junto com o filtro ($filter) para a função de callback;
         * essa função primeiro valida se o $filter atende o if e depois adiciona o where que de fato será aglutinado à consultado
         */
    }

    public function createNew(CreateUserDTO $dto): User
    {
        $data = (array) $dto;
        $data['password'] = bcrypt($data['password']);
        return $this->user->create($data);
    }

    public function findById(string $id, array $permissions = []): ?user
    {
        return $this->user->with($permissions)->find($id);
    }

    public function findByEmail(string $email): ?user
    {
        return $this->user->where('email', $email)->first();
    }

    public function update(EditUserDTO $dto): bool
    {
        if (!$user = $this->findById($dto->id)) {
            return false;
        }
        $data = (array) $dto;
        unset($data['password']);
        if ($dto->password !== null) {
            $data['password'] = bcrypt($dto->password);
        }
        return $user->update($data); // Retorna true ou false
    }

    public function delete(string $id): bool
    {
        if (!$user = $this->findById($id)) {
            return false;
        }
        return $user->delete();
    }

    public function syncPermissions(string $id, array $permissions): ?bool
    {
        if (!$user = $this->findById($id)) {
            return null;
        }
        $user->permissions()->sync($permissions);
        return true;
    }

    public function getPermissions(string $id): ?array
    {
        if (!$user = $this->findById($id)) {
            return null;
        }
        return $user->permissions->toArray();
    }

    public function hasPermission(User $user, string $permission): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return $user->permissions->contains('name', $permission);
    }
}

/**
 * É uma extensão do model de usuários;
 * serve apenas para deixar a model limpa;
 * traz toda as querys(consultas no banco) pra cá.
 */
