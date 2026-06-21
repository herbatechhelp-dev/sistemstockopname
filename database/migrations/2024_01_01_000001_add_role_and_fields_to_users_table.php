<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['superadmin', 'admin', 'team_leader', 'petugas_so'])->default('petugas_so')->after('name');
            $table->string('full_name')->nullable()->after('role');
            $table->string('phone')->nullable()->after('full_name');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'full_name', 'phone', 'is_active']);
        });
    }
};
