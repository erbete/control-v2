<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Control\Infrastructure\Permission;
use Control\Infrastructure\User;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->seedPermissions();
        $this->seedUsers();
    }

    private function seedPermissions()
    {
        // Permissions
        $permissions = [];

        $admin = [
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => 'Admin access to the app'
        ];
        $rebinding = [
            'name' => 'Rebinding',
            'slug' => 'rebinding',
            'description' => 'Users doing rebinding'
        ];
        $permissions[] = $rebinding;
        $permissions[] = $admin;

        Permission::upsert($permissions, ['slug']);
    }

    private function seedUsers()
    {
        $admin = User::updateOrCreate(['email' => 'dev@admin.no'], [
            'name' => 'Control Admin',
            'email' => 'dev@admin.no',
            'password' => 'password',
        ]);

        $user = User::updateOrCreate(['email' => 'dev@user.no'], [
            'name' => 'Control User',
            'email' => 'dev@user.no',
            'password' => 'password',
        ]);

        // associate permissions to the users
        $adminPermission = Permission::where('slug', '=', 'admin')->first();
        $rebindingPermission = Permission::where('slug', '=', 'rebinding')->first();
        $admin->permissions()->sync([$adminPermission->id, $rebindingPermission->id]);
        $user->permissions()->sync($rebindingPermission);
    }
}
