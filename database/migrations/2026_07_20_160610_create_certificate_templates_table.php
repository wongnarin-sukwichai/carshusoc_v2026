<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('service_center_code');
            $table->string('name');
            $table->string('background_image_path')->nullable();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('signatory1_name');
            $table->string('signatory1_title');
            $table->string('signatory2_name')->nullable();
            $table->string('signatory2_title')->nullable();
            $table->string('signatory3_name')->nullable();
            $table->string('signatory3_title')->nullable();
            $table->string('border_color')->default('#4f46e5');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
