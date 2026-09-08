<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories: tolerance per kategori + softDeletes
        Schema::table('categories', function (Blueprint $table) {
            $table->decimal('tolerance_percentage', 5, 2)->nullable()->after('description');
            $table->softDeletes()->after('updated_at');
        });

        // UoMs: softDeletes
        Schema::table('uoms', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Items: softDeletes
        Schema::table('items', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Locations: softDeletes
        Schema::table('locations', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Session snapshots: source
        Schema::table('session_snapshots', function (Blueprint $table) {
            $table->string('source')->default('simulated')->after('system_qty');
        });

        // So entries: parent_entry_id, is_recount, unique index
        Schema::table('so_entries', function (Blueprint $table) {
            $table->foreignId('parent_entry_id')->nullable()->after('id')->constrained('so_entries')->nullOnDelete();
            $table->boolean('is_recount')->default(false)->after('status');
            // unique per sesi+lokasi+item+batch (batch_code nullable -> unique index dengan batch_code not null; untuk null kita cek di aplikasi)
            $table->unique(['session_id', 'location_id', 'item_id', 'batch_code'], 'uniq_entry_session_loc_item_batch');
        });

        // Team location allocations: completed_at
        Schema::table('team_location_allocations', function (Blueprint $table) {
            $table->timestamp('completed_at')->nullable()->after('status');
        });

        // Inventory adjustments (B1)
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('so_sessions')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->decimal('system_qty', 12, 2);
            $table->decimal('fisik_qty', 12, 2);
            $table->decimal('adjustment_qty', 12, 2);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');

        Schema::table('team_location_allocations', function (Blueprint $table) {
            $table->dropColumn('completed_at');
        });

        Schema::table('so_entries', function (Blueprint $table) {
            $table->dropUnique('uniq_entry_session_loc_item_batch');
            $table->dropForeign(['parent_entry_id']);
            $table->dropColumn(['parent_entry_id', 'is_recount']);
        });

        Schema::table('session_snapshots', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('uoms', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('tolerance_percentage');
            $table->dropSoftDeletes();
        });
    }
};
