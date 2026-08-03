<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->date('registration_open_at')->nullable()->after('end_date');
            $table->date('registration_close_at')->nullable()->after('registration_open_at');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->date('registration_open_at')->nullable()->after('exam_date');
            $table->date('registration_close_at')->nullable()->after('registration_open_at');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['registration_open_at', 'registration_close_at']);
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['registration_open_at', 'registration_close_at']);
        });
    }
};
