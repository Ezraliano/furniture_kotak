<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Buat Roles
        $adminRole = Role::create(['name' => 'Administrator']);
        $stafRole = Role::create(['name' => 'Staf Produksi']);

        // Buat Permissions (contoh)
        Permission::create(['name' => 'kelola pengguna']);
        Permission::create(['name' => 'kelola peran']);
        Permission::create(['name' => 'kelola pesanan']);

        // Beri permission ke role
        $adminRole->givePermissionTo(['kelola pengguna', 'kelola peran', 'kelola pesanan']);
        $stafRole->givePermissionTo('kelola pesanan');
    }
}
