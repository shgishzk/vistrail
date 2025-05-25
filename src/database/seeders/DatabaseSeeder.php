<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Area;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => Hash::make('Vistrail123!'),
        ]);

        Admin::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Vistrail123!'),
        ]);

        User::factory()->count(10)->create();
        Area::factory()->count(10)->create();
    }
}
