<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'GTonics Developers',
            'email' => 'developers@gtonics.com',
            'username' => 'Admin',
            'password' => '$2y$10$lDrNlab/0AxFzvXvcaqJ0.rme1cixxASz6pBsIRebFeUqyv/65122',
            'point_who' => '10',
            'point_use' => '20'
        ]);
    }
}
