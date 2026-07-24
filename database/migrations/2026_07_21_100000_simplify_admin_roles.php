<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Collapses admins.role from 4 values (super_admin, translation_manager,
     * finance_officer, staff) to 2 (admin, staff) — the middle two operational
     * roles were always equal in practice (see EnsureAdminHasRole), so they
     * fold into "staff"; super_admin becomes plain "admin".
     *
     * Dropping the enum constraint in favor of a plain string keeps this
     * migration portable across MySQL (dev/prod) and SQLite (tests) without
     * driver-specific raw DDL — nothing in the app relies on the DB itself
     * rejecting an out-of-range role value.
     */
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('role')->default('staff')->change();
        });

        DB::table('admins')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::table('admins')->whereIn('role', ['translation_manager', 'finance_officer'])->update(['role' => 'staff']);
    }

    public function down(): void
    {
        DB::table('admins')->where('role', 'admin')->update(['role' => 'super_admin']);

        Schema::table('admins', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'translation_manager', 'finance_officer', 'staff'])->default('staff')->change();
        });
    }
};
