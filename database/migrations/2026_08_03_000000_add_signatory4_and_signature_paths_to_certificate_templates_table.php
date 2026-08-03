<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->string('signatory4_name')->nullable()->after('signatory3_title');
            $table->string('signatory4_title')->nullable()->after('signatory4_name');
            $table->string('signatory1_signature_path')->nullable()->after('signatory1_title');
            $table->string('signatory2_signature_path')->nullable()->after('signatory2_title');
            $table->string('signatory3_signature_path')->nullable()->after('signatory3_title');
            $table->string('signatory4_signature_path')->nullable()->after('signatory4_title');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn([
                'signatory4_name',
                'signatory4_title',
                'signatory1_signature_path',
                'signatory2_signature_path',
                'signatory3_signature_path',
                'signatory4_signature_path',
            ]);
        });
    }
};
