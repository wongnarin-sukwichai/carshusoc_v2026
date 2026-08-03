<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->string('room')->nullable()->after('exam_id');
            $table->string('seat_number')->nullable()->after('room');
        });
    }

    public function down(): void
    {
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->dropColumn(['room', 'seat_number']);
        });
    }
};
