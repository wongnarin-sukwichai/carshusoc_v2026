<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending_payment', 'registered', 'scored', 'cancelled'])->default('pending_payment');
            $table->unsignedTinyInteger('listening_score')->nullable();
            $table->unsignedTinyInteger('reading_score')->nullable();
            $table->unsignedTinyInteger('conversation_score')->nullable();
            $table->unsignedTinyInteger('grammar_score')->nullable();
            $table->unsignedSmallInteger('total_score')->nullable();
            $table->string('cefr_level')->nullable();
            $table->foreignId('score_scale_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('wants_mail_delivery')->default(false);
            $table->decimal('mail_delivery_fee_charged', 10, 2)->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->boolean('certificate_issued')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_registrations');
    }
};
