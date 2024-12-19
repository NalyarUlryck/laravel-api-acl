<?php

namespace App\DTO\Users;

readonly class EditUserDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $password = null // esse "?" torna a senha opcional.
    ) {}
}
