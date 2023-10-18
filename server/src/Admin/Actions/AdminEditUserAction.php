<?php

namespace Control\Admin\Actions;

use Illuminate\Http\Request;

use Control\Infrastructure\Permission;
use Control\Infrastructure\User;

class AdminEditUserAction
{
    public function execute(User $user, Request $request)
    {
        $user->name = $request->name;
        $user->email = $request->email;
        $user->blocked = $request->blocked;
        $user->blocked_at = null;
        if ($request->blocked) $user->blocked_at = now();
        if ($request->has('permissions')) {
            $permissionIds = [];
            foreach ($request->permissions as $slug) {
                $permission = Permission::where('slug', '=', $slug)->first();
                if ($permission) {
                    $permissionIds[] = $permission->id;
                }
            }

            $user->permissions()->sync($permissionIds);
        }

        $user->save();
    }
}
