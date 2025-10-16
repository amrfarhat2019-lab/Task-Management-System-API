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
            'name' => 'amr',
            'email' => 'manager@softxpert.com',
            'password' => Hash::make('123123123'),
            'role' => 'manager'
        ]);

        
        User::create([
            'name' => 'ahmed',
            'email' => 'user@softxpert.com',
            'password' => Hash::make('123123123'),
            'role' => 'user'
        ]);

        User::create([
            'name' => 'ali',
            'email' => 'user1@softxpert.com',
            'password' => Hash::make('123123123'),
            'role' => 'user'
        ]);
    }
}
