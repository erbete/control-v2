<?php

namespace Control\Common\Mappers;

use Illuminate\Support\Collection;

class UsersMapper
{
    public static function map(Collection $users): array
    {
        $mappedUsers = [];
        foreach ($users as $user) $mappedUsers[] = UserMapper::map($user);
        return $mappedUsers;
    }
}
