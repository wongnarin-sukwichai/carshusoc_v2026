<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('certifiable');
            $table->foreignId('certificate_template_id')->constrained();
            $table->foreignId('score_scale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('verification_hash')->unique();
            $table->timestamp('issued_at');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
