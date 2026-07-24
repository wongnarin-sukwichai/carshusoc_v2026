<?php

namespace Database\Factories;

use App\Models\CertificateTemplate;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certificate>
 */
class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'certifiable_type' => CourseEnrollment::class,
            'certifiable_id' => CourseEnrollment::factory(),
            'certificate_template_id' => CertificateTemplate::factory(),
            'verification_hash' => Str::random(40),
            'issued_at' => now(),
        ];
    }
}
