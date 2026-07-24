<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'EN-'.fake()->unique()->numberBetween(10, 999),
            'name_th' => 'ภาษาอังกฤษเพื่อการสื่อสาร',
            'name_en' => 'English Communication',
            'language' => 'อังกฤษ',
            'level' => 1,
            'price' => 2000,
            'is_visible' => true,
        ];
    }
}
