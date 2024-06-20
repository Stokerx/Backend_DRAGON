<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create(['name' => 'admin']);
        $role1 =  Role::create(['name' => 'dragon']);
        $role2 = Role::create(['name' => 'user']);


        Permission::create(['name' => 'admin.edit series'])->assignRole($role);
        Permission::create(['name' => 'admin.delete series'])->assignRole($role);
        Permission::create(['name' => 'admin.activity series'])->assignRole($role);
        Permission::create(['name' => 'admin activity system'])->assignRole($role);
        Permission::create(['name' => 'admin.create.user'])->assignRole($role);
        Permission::create(['name' => 'admin.role.user'])->assignRole($role);
        Permission::create(['name' => 'admin.edit.user'])->assignRole($role);
        Permission::create(['name' => 'admin.delete.user'])->assignRole($role);
        Permission::create(['name' => 'admin.history.all.user'])->assignRole($role);
        Permission::create(['name' => 'admin.register.series'])->assignRole($role);

        Permission::create(['name' => 'dragon.activity series'])->assignRole($role1);
        Permission::create(['name' => 'dragon.History.all.user'])->assignRole($role1);;
        Permission::create(['name' => 'dragon.register.series'])->assignRole($role1);;
        Permission::create(['name' => 'dragon.edit.user'])->assignRole($role1);;

        Permission::create(['name' => 'user.edit.user'])->assignRole($role2);
        Permission::create(['name' => 'user.register.series'])->assignRole($role2);;
        Permission::create(['name' => 'user.History.user'])->assignRole($role2);;
    }

    private function createPermission(string $name): void
    {
        if (!Permission::where('name', $name)->first()) {
            Permission::create(['name' => $name]);
        }
    }
}