<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_th');
            $table->string('name_en');
            $table->string('language');
            $table->unsignedInteger('level');
            $table->decimal('price', 10, 2);
            $table->foreignId('prerequisite_course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('location')->nullable();
            $table->boolean('requires_receipt')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->foreignId('certificate_template_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
