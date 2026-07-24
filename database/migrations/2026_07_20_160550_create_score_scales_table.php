<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('version');
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_scales');
    }
};
