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
        Schema::table('users', function (Blueprint $table) {
            // Azure AD Integration Fields
            if (!Schema::hasColumn('users', 'azure_id')) {
                $table->string('azure_id')->nullable()
                    ->comment('Azure AD User Object ID');
                $table->index('azure_id', 'idx_users_azure_id');
            }
            
            if (!Schema::hasColumn('users', 'azure_upn')) {
                $table->string('azure_upn')->nullable()
                    ->comment('Azure AD User Principal Name (email)');
                $table->unique('azure_upn', 'idx_users_azure_upn_unique');
            }
            
            if (!Schema::hasColumn('users', 'azure_display_name')) {
                $table->string('azure_display_name')->nullable()
                    ->comment('Display name in Azure AD');
            }

            // Additional User Profile Fields
            if (!Schema::hasColumn('users', 'job_title')) {
                $table->string('job_title')->nullable()
                    ->comment('User job title/position');
            }
            
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()
                    ->comment('User department');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            if (Schema::hasColumn('users', 'azure_id')) {
                $table->dropIndex('idx_users_azure_id');
            }
            if (Schema::hasColumn('users', 'azure_upn')) {
                $table->dropUnique('idx_users_azure_upn_unique');
            }
            
            // Drop columns
            $dropColumns = [];
            if (Schema::hasColumn('users', 'azure_id')) $dropColumns[] = 'azure_id';
            if (Schema::hasColumn('users', 'azure_upn')) $dropColumns[] = 'azure_upn';
            if (Schema::hasColumn('users', 'azure_display_name')) $dropColumns[] = 'azure_display_name';
            if (Schema::hasColumn('users', 'job_title')) $dropColumns[] = 'job_title';
            if (Schema::hasColumn('users', 'department')) $dropColumns[] = 'department';
            
            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};