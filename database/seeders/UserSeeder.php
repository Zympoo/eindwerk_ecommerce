<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $customerRole = Role::where('name', 'customer')->first();

        User::factory()->create([
            'role_id' => $adminRole->id,
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'role_id' => $adminRole->id,
            'name' => 'Warre Neirinck',
            'email' => 'warre.neirinck@gmail.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->count(10)->create([
            'role_id' => $customerRole->id,
        ]);
    }
}