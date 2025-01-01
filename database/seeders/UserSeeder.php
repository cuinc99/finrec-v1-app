<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
            'role' => UserRoleEnum::ADMIN,
        ]);

        User::create([
            'name' => 'User',
            'email' => 'user@user.com',
            'password' => bcrypt('password'),
            'role' => UserRoleEnum::USER,
        ]);

        User::create([
            'name' => 'Demo',
            'email' => 'demo@demo.com',
            'password' => bcrypt('password'),
            'role' => UserRoleEnum::FREE,
        ]);
    }
}
