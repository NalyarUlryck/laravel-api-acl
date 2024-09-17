<?php

namespace App\Repositories;

use App\Models\Permission;

class PermissionRepository
{
    public function __construct(protected Permission $permission) {}
}

/**
 * É uma extensão do model de permisssão;
 * serve apenas para deixar a model limpa;
 * traz toda as querys(consultas no banco) pra cá.
*/
