<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // Create super admin role if it doesn't exist
        $superRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        // Create permission for super admin
        $permissions = [
            ['name' => 'view_role'],
            ['name' => 'view_any_role'],
            ['name' => 'create_role'],
            ['name' => 'update_role'],
            ['name' => 'delete_role'],
            ['name' => 'delete_any_role']
        ];
        
        foreach ($permissions as $permission) {
            $newPermission = Permission::create($permission);
            $superRole->givePermissionTo($newPermission);
        }

        // Create super admin user
        $superAdmin = \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'password' => Hash::make('shutdown199'),
            'email' => 'superadmin@gmail.com',
        ]);

        $superAdminRole = Role::where('name', 'super_admin')->firstOrFail();
        $superAdmin->assignRole($superAdminRole);

        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        // Create admin user
        $admin = \App\Models\User::factory()->create([
            'name' => 'Admin',
            'password' => Hash::make('shutdown199'),
            'email' => 'admin@gmail.com',
        ]);

        // Assign admin role to admin user
        $admin->assignRole($adminRole);
    }
}