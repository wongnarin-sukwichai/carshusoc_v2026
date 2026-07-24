<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CertificateTemplate>
 */
class CertificateTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_center_code' => 'training',
            'name' => fake()->words(3, true),
            'title' => 'Certificate of Achievement',
            'subtitle' => null,
            'signatory1_name' => fake()->name(),
            'signatory1_title' => 'Director',
            'border_color' => '#4f46e5',
            'is_default' => false,
        ];
    }
}
