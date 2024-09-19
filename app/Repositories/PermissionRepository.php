<?php

namespace App\Repositories;

use App\DTO\Permissions\CreatePermissionDTO;
use App\DTO\Permissions\EditPermissionDTO;
use App\Models\Permission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PermissionRepository
{
    public function __construct(protected Permission $permission) {}

    public function getPaginate(int $totalPerPage = 15,int $page = 1, string $filter = ''): LengthAwarePaginator
    {
        return $this->permission->where(function ($query) use ($filter) {
            if ($filter !== '') {
                $query->where('name', 'like', "%{$filter}%");
            }
        })->paginate($totalPerPage, ['*'], 'page', $page);
        /**
         * Estou passando a consulta($query) junto com o filtro ($filter) para a função de callback;
         * essa função primeiro valida se o $filter atende o if e depois adiciona o where que de fato será aglutinado à consultado
         */
    }

    public function createNew(CreatePermissionDTO $dto) : Permission
    {
        return $this->permission->create((array) $dto);
    }

    public function findById(string $id): ?permission
    {
        return $this->permission->find($id);
    }

    public function update(EditPermissionDTO $dto): bool
    {
        if (!$permission = $this->findById($dto->id)) {
            return false;
        }

        return $permission->update((array) $dto); // Retorna true ou false
    }

    public function delete(string $id):bool
    {
        if (!$permission = $this->findById($id)) {
            return false;
        }
        return $permission->delete();
    }
}

/**
 * É uma extensão do model de permisssão;
 * serve apenas para deixar a model limpa;
 * traz toda as querys(consultas no banco) pra cá.
*/
