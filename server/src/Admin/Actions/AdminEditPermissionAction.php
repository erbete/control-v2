<?php

namespace Control\Admin\Actions;

use Illuminate\Http\Request;
use Control\Infrastructure\Permission;

class AdminEditPermissionAction
{
    public function execute(Permission $permission, Request $request)
    {
        $permission->name = $request->name;
        $permission->slug = $request->slug;
        $permission->description = $request->description;
        $permission->save();
    }
}
