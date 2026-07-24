<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_th',
        'name_en',
        'description_th',
        'description_en',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }
}
