<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            ['role_name' => 'Admin'],
            ['role_name' => 'PIC'],
            ['role_name' => 'Guru'],
            ['role_name' => 'Guest'],
        ]);
    }
}