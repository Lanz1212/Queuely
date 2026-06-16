<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\Gate;
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
        // Admin
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Operator
        User::factory()->create([
            'name' => 'Operator Gudang',
            'email' => 'operator@admin.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
        ]);

        // Services
        Service::insert([
            ['name' => 'Muat Produk',   'code_prefix' => 'M', 'color' => '#10B981', 'estimated_time' => 30, 'activity_type' => 'muat'],
            ['name' => 'Bongkar Produk','code_prefix' => 'B', 'color' => '#3B82F6', 'estimated_time' => 45, 'activity_type' => 'bongkar'],
            ['name' => 'Retur Barang',  'code_prefix' => 'R', 'color' => '#F59E0B', 'estimated_time' => 20, 'activity_type' => 'retur'],
        ]);

        // Gates
        Gate::insert([
            ['name' => 'Gate 1', 'status' => 'ready', 'notes' => ''],
            ['name' => 'Gate 2', 'status' => 'ready', 'notes' => ''],
            ['name' => 'Gate 3', 'status' => 'maintenance', 'notes' => 'Under repair'],
        ]);
    }
}
