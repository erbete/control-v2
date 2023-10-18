<?php

namespace Control\Common\DTO;

class PermissionData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $description,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly array $users,
    ) {
    }
}
