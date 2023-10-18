<?php

namespace Control\Common\Mappers;

use Control\Common\DTO\UserData;
use Control\Infrastructure\User;

class UserMapper
{
    public static function map(User $user): UserData
    {
        // map permissions
        $permissions = [];
        foreach ($user->permissions as $permission) {
            $permissions[] = (object)[
                'id' => (string)$permission->id,
                'name' => $permission->name,
                'slug' => $permission->slug,
                'description' => $permission->description,
                'createdAt' => $permission->created_at,
                'updatedAt' => $permission->updated_at,
            ];
        }

        return new UserData(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            blocked: is_null($user->blocked) ? false : $user->blocked,
            blockedAt: (string)$user->blocked_at,
            permissions: $permissions,
            createdAt: $user->created_at,
            updatedAt: $user->updated_at,
        );
    }
}
