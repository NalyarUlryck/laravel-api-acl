<?php

namespace App\DTO\Permissions;

readonly class CreatePermissionDTO
{
    public function __construct(
        public string $name,
        public string $description = ''
    ) {}
}
