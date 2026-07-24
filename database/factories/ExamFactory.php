<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exam>
 */
class ExamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'EX-'.fake()->unique()->numberBetween(10, 999),
            'type' => 'EPT',
            'name_th' => 'การทดสอบวัดความรู้ความสามารถด้านภาษาอังกฤษ',
            'name_en' => 'English Proficiency Test',
            'price' => 800,
            'exam_date' => now()->addMonth(),
            'is_visible' => true,
        ];
    }
}
