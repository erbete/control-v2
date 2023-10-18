<?php

namespace Control\Admin\Actions;

use Illuminate\Http\Request;
use Control\Infrastructure\Permission;

class AdminCreatePermissionAction
{
    public function execute(Request $request): Permission
    {
        $permission = Permission::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
        ]);

        return $permission;
    }
}
