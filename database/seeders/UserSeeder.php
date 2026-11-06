<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::insert([

            // =======================
            // ADMIN
            // =======================

            [
                'role_id' => 1,
                'name' => 'Administrator',
                'email' => 'admin@rapi.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =======================
            // PIC
            // =======================

            [
                'role_id' => 2,
                'name' => 'Budi Santoso',
                'email' => 'budi@rapi.com',
                'password' => Hash::make('password'),
                'phone' => '62123456789',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'role_id' => 2,
                'name' => 'Agus Setiawan',
                'email' => 'agus@rapi.com',
                'password' => Hash::make('password'),
                'phone' => '62987654321',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'role_id' => 2,
                'name' => 'Rina Wulandari',
                'email' => 'rina@rapi.com',
                'password' => Hash::make('password'),
                'phone' => '622132436576',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // =======================
            // GURU
            // =======================

            [
                'role_id' => 3,
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@rapi.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'role_id' => 3,
                'name' => 'Dewi Lestari',
                'email' => 'dewi@rapi.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'role_id' => 3,
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@rapi.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'role_id' => 3,
                'name' => 'Eko Prasetyo',
                'email' => 'eko@rapi.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'role_id' => 3,
                'name' => 'Lina Marlina',
                'email' => 'lina@rapi.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'role_id' => 3,
                'name' => 'Rudi Hartono',
                'email' => 'rudi@rapi.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'role_id' => 3,
                'name' => 'Maya Sari',
                'email' => 'maya@rapi.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'role_id' => 3,
                'name' => 'Andi Wijaya',
                'email' => 'andi@rapi.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}