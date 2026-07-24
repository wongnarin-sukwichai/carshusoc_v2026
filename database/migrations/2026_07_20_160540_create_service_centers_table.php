<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_centers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_th');
            $table->string('name_en');
            $table->text('description_th')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_centers');
    }
};
