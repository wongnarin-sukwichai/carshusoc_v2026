<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_scale_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('score_scale_id')->constrained()->cascadeOnDelete();
            $table->string('cefr_level');
            $table->unsignedInteger('toeic_min')->nullable();
            $table->unsignedInteger('toeic_max')->nullable();
            $table->unsignedInteger('ept_min')->nullable();
            $table->unsignedInteger('ept_max')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_scale_bands');
    }
};
