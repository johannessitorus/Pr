<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Ganti dengan password yang kuat
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Dosen User',
            'username' => 'dosen01',
            'email' => 'dosen01@example.com',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Frans',
            'username' => 'mahasiswa01',
            'email' => 'mahasiswa01@example.com',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'email_verified_at' => now(),
        ]);
    }
}
