<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('so_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('status', ['draft', 'active', 'completed', 'closed'])->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('session_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('so_sessions')->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('restrict');
            $table->foreignId('location_id')->constrained()->onDelete('restrict');
            $table->decimal('system_qty', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('session_id')->constrained('so_sessions')->onDelete('cascade');
            $table->foreignId('team_leader_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['team_id', 'user_id']);
        });

        Schema::create('team_location_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('restrict');
            $table->enum('status', ['assigned', 'completed', 'locked'])->default('assigned');
            $table->timestamps();
            $table->unique(['team_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_location_allocations');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('session_snapshots');
        Schema::dropIfExists('so_sessions');
    }
};
