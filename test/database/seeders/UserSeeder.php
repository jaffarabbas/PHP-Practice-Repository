<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Jaffar', 'email' => 'jaffar@example.com', 'password' => bcrypt('password123')],
            ['name' => 'Ahmed', 'email' => 'ahmed@example.com', 'password' => bcrypt('password123')],
            ['name' => 'Ali', 'email' => 'ali@example.com', 'password' => bcrypt('password123')]
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
