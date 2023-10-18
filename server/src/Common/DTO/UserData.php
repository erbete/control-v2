<?php

namespace Control\Common\DTO;

class UserData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly bool $blocked,
        public readonly ?string $blockedAt,
        public readonly array $permissions,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {
    }
}
