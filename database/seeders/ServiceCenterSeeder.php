<?php

namespace Database\Seeders;

use App\Models\ServiceCenter;
use Illuminate\Database\Seeder;

class ServiceCenterSeeder extends Seeder
{
    public function run(): void
    {
        $centers = [
            [
                'code' => 'training',
                'name_th' => 'ศูนย์อบรมภาษา',
                'name_en' => 'Language Training Center',
            ],
            [
                'code' => 'exam',
                'name_th' => 'ศูนย์สอบภาษาอังกฤษ',
                'name_en' => 'English Exam Center',
            ],
            [
                'code' => 'translation',
                'name_th' => 'ศูนย์แปลเอกสาร',
                'name_en' => 'Translation Center',
            ],
        ];

        foreach ($centers as $center) {
            ServiceCenter::firstOrCreate(
                ['code' => $center['code']],
                [
                    'name_th' => $center['name_th'],
                    'name_en' => $center['name_en'],
                    'is_visible' => true,
                ]
            );
        }
    }
}
