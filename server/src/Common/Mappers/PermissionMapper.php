<?php

namespace Control\Common\Mappers;

use Illuminate\Support\Collection;

use Control\Common\DTO\PermissionData;
use Control\Common\DTO\UserData;
use Control\Infrastructure\Permission;

class PermissionMapper
{
    public static function map(Collection | Permission $data): array | PermissionData
    {
        if (!($data instanceof Collection)) {
            // map users
            $users = [];
            foreach ($data->users as $user) {
                $users[] = new UserData(
                    id: (string)$user->id,
                    name: $user->name,
                    email: $user->email,
                    blocked: $user->blocked,
                    blockedAt: $user->blocked_at,
                    permissions: $user->permissions->toArray(),
                    createdAt: $user->created_at,
                    updatedAt: $user->updated_at,
                );
            }

            return new PermissionData(
                id: $data->id,
                name: $data->name,
                slug: $data->slug,
                description: $data->description,
                createdAt: $data->created_at,
                updatedAt: $data->updated_at,
                users: $users,
            );
        }

        $permissions = [];
        foreach ($data as $property) {
            // map users
            $users = [];
            foreach ($property->users as $user) {
                $users[] = new UserData(
                    id: (string)$user->id,
                    name: $user->name,
                    email: $user->email,
                    blocked: $user->blocked,
                    blockedAt: $user->blocked_at,
                    permissions: $user->permissions->toArray(),
                    createdAt: $user->created_at,
                    updatedAt: $user->updated_at,
                );
            }

            $permissions[] = new PermissionData(
                id: $property->id,
                name: $property->name,
                slug: $property->slug,
                description: $property->description,
                createdAt: $property->created_at,
                updatedAt: $property->updated_at,
                users: $users,
            );
        }

        return $permissions;
    }
}
