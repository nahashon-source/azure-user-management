<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_modules', function (Blueprint $table) {
            // First, add location column if it doesn't exist
            if (!Schema::hasColumn('user_modules', 'location')) {
                $table->string('location')->nullable()
                    ->comment('Location/branch where user has module access');
            }

            // External system integration tracking
            $table->string('external_system_id')->nullable()
                ->comment('User ID from external system (SCM, FITGP, etc.)');
            
            $table->enum('external_sync_status', ['pending', 'synced', 'failed'])
                ->default('pending')
                ->comment('Status of external system synchronization');
            
            $table->timestamp('external_synced_at')->nullable()
                ->comment('When external sync was completed successfully');
            
            $table->text('external_sync_error')->nullable()
                ->comment('Error message if external sync failed');

            // Add index for common queries
            $table->index('external_sync_status', 'idx_external_sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_modules', function (Blueprint $table) {
            $table->dropIndex('idx_external_sync_status');
            $table->dropColumn([
                'external_system_id',
                'external_sync_status',
                'external_synced_at',
                'external_sync_error',
            ]);
            
            // Only drop location if it was added by this migration
            if (Schema::hasColumn('user_modules', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};