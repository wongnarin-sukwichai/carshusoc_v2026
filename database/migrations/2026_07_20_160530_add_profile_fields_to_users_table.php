<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('address')->nullable()->after('phone');
            $table->enum('identity_type', ['national_id', 'passport'])->nullable()->after('address');
            $table->string('identity_number')->nullable()->after('identity_type');
            $table->enum('auth_provider', ['local', 'msu_sso'])->default('local')->after('identity_number');
            $table->string('sso_subject')->nullable()->unique()->after('auth_provider');
            $table->string('msu_student_id')->nullable()->after('sso_subject');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'address',
                'identity_type',
                'identity_number',
                'auth_provider',
                'sso_subject',
                'msu_student_id',
            ]);
        });
    }
};
