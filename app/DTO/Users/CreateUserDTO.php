<?php

namespace App\DTO\Users;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {}
}
