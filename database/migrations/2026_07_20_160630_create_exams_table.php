<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type');
            $table->string('name_th');
            $table->string('name_en');
            $table->decimal('price', 10, 2);
            $table->date('exam_date');
            $table->string('location')->nullable();
            $table->boolean('requires_receipt')->default(false);
            $table->boolean('mail_delivery_available')->default(false);
            $table->decimal('mail_delivery_fee', 10, 2)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->foreignId('certificate_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('score_scale_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
