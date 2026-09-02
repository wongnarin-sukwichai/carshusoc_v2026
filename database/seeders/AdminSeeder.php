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
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'เจ้าหน้าที่ดูแลระบบ',
                'password' => Hash::make('1234'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
