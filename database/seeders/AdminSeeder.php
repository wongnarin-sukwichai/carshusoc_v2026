<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::firstOrCreate(
            ['email' => 'wongnarin.s@msu.ac.th'],
            [
                'name' => 'วงศ์นรินทร์ สุขวิชัย',
                'password' => Hash::make('w123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
