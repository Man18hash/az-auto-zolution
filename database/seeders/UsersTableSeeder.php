<?php

// database/seeders/UsersTableSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        // Cashier
        User::create([
            'name'     => 'Diana',
            'email'    => 'cashier@az.com',
            'password' => Hash::make('password123'),
            'role'     => 'cashier',
        ]);
        User::create([
            'name'     => 'Grace',
            'email'    => 'cashier1@az.com',
            'password' => Hash::make('password123'),
            'role'     => 'cashier',
        ]);
        User::create([
            'name'     => 'Belmonte',
            'email'    => 'cashier2@az.com',
            'password' => Hash::make('password123'),
            'role'     => 'cashier',
        ]);

        // Admin
        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@az.com',
            'password' => Hash::make('password123'),
            'role'     => 'admin',
        ]);
    }
}

