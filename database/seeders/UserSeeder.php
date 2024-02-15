<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Crear tres usuarios de ejemplo
        User::create([
            'name' => 'Valeria Barona',
            'email' => 'valeria@example.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Usuario 1',
            'email' => 'usuario1@example.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
