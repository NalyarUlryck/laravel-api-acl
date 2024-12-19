<?php

namespace App\DTO\Permissions;

readonly class EditPermissionDTO extends CreatePermissionDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description
    ) {}
}
