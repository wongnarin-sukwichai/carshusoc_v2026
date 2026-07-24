<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('source_lang');
            $table->string('target_lang');
            $table->enum('status', ['submitted', 'quote_sent', 'translating', 'completed'])->default('submitted');
            $table->decimal('estimated_price', 10, 2)->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('source_file_path')->nullable();
            $table->string('translated_file_path')->nullable();
            $table->foreignId('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_requests');
    }
};
