<?php

namespace Control\Admin\Http;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Control\Common\Controller;
use Control\Admin\Actions\AdminRegisterUserAction;
use Control\Admin\Actions\AdminEditUserAction;
use Control\Admin\Actions\AdminCreatePermissionAction;
use Control\Admin\Actions\AdminEditPermissionAction;
use Control\Common\Mappers\UsersMapper;
use Control\Common\Mappers\PermissionMapper;
use Control\Common\Mappers\UserMapper;
use Control\Infrastructure\User;
use Control\Infrastructure\Permission;

class AdminController extends Controller
{
    public function user(Request $request)
    {
        return response()->json(UserMapper::map(User::findOrFail($request->id)));
    }

    public function users()
    {
        return response()->json(UsersMapper::map(User::all()));
    }

    public function registerUser(Request $request, AdminRegisterUserAction $registerUser)
    {
        $request->validate(User::registerRules(), User::$registerMessages);

        return response()->json(
            UserMapper::map($registerUser->execute($request)),
            Response::HTTP_CREATED,
        );
    }

    public function editUser(Request $request, AdminEditUserAction $editUser)
    {
        $user = User::findOrFail($request->route('id'));
        $request->validate(User::editRules($user), User::$editMessages);
        $editUser->execute($user, $request);

        return response()->json(UserMapper::map($user));
    }

    public function permissions()
    {
        return response()->json(PermissionMapper::map(Permission::with('users')->get()));
    }

    public function permission(Request $request)
    {
        return response()->json(PermissionMapper::map(Permission::findOrFail($request->id)));
    }

    public function createPermission(Request $request, AdminCreatePermissionAction $createPermission)
    {
        $request->validate(Permission::$createRules, Permission::$createMessages);
        $newPermission = $createPermission->execute($request, Permission::class);
        return response()->json(PermissionMapper::map($newPermission), Response::HTTP_CREATED);
    }

    public function editPermission(Request $request, AdminEditPermissionAction $editPermission)
    {
        $permission = Permission::findOrFail($request->route('id'));
        $request->validate(Permission::editRules($permission), Permission::$createMessages);
        $editPermission->execute($permission, $request);

        return response()->json(PermissionMapper::map($permission));
    }

    public function deletePermission(Request $request)
    {
        $permission = Permission::findOrFail($request->id);
        $permission->delete();
        return response()->noContent();
    }

    public function detachUserFromPermission(Request $request)
    {
        $request->validate(Permission::$detachRules, Permission::$detachMessages);
        $permission = Permission::find($request->permissionId);
        $permission->users()->detach($request->userId);

        return response()->noContent();
    }
}
