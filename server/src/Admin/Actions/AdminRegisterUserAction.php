<?php

namespace Control\Admin\Actions;

use Illuminate\Http\Request;

use Control\Infrastructure\Permission;
use Control\Infrastructure\User;

class AdminRegisterUserAction
{
    public function execute(Request $request): User
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($request->permissions && count($request->permissions) > 0) {
            $permissionIds = [];
            foreach ($request->permissions as $slug) {
                $permission = Permission::where('slug', '=', $slug)->first();
                if ($permission) {
                    $permissionIds[] = $permission->id;
                }
            }

            $user->permissions()->sync($permissionIds);
        }

        return $user;
    }
}
