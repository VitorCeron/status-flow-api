<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Enums\TimezoneEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@statusflow.com'],
            [
                'name' => 'Admin',
                'password' => '!@#Abc123456789',
                'timezone' => TimezoneEnum::UTC->value,
                'role' => RoleEnum::ADMIN->value,
            ]
        );
    }
}
