<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('so_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('so_sessions')->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('restrict');
            $table->foreignId('petugas_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('item_id')->constrained()->onDelete('restrict');
            $table->foreignId('location_id')->constrained()->onDelete('restrict');
            $table->string('uom');
            $table->string('batch_code')->nullable();
            $table->decimal('fisik_qty', 12, 2);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending', 'verified', 'recount_requested', 'recount_done'])->default('pending');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('so_entry_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_entry_id')->constrained('so_entries')->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->decimal('old_fisik_qty', 12, 2);
            $table->decimal('new_fisik_qty', 12, 2);
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('recount_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('so_entry_id')->constrained('so_entries')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_team_id')->constrained('teams')->onDelete('restrict');
            $table->foreignId('assigned_petugas_id')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
            $table->index('action');
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('recount_requests');
        Schema::dropIfExists('so_entry_revisions');
        Schema::dropIfExists('so_entries');
    }
};
