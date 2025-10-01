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
        Schema::table('modules', function (Blueprint $table) {
            // Azure AD Group Integration
            $table->string('azure_group_id')->nullable()
                ->comment('Azure AD Security Group ID for this module');
            
            // Azure Enterprise Application Integration
            $table->string('azure_enterprise_app_id')->nullable()
                ->comment('Azure Enterprise Application (Service Principal) ID');
            
            // Integration Flags
            $table->boolean('requires_group_assignment')->default(false)
                ->comment('Whether users need to be added to Azure AD Group');
            
            $table->boolean('requires_app_role_assignment')->default(false)
                ->comment('Whether users need app role assignment in enterprise app');
            
            // External API Integration
            $table->string('external_api_endpoint')->nullable()
                ->comment('External API endpoint for user provisioning');
            
            $table->enum('api_auth_method', ['none', 'bearer', 'api_key', 'oauth'])
                ->default('none')
                ->comment('Authentication method for external API');
            
            $table->text('api_credentials')->nullable()
                ->comment('Encrypted API credentials (token, key, etc.)');
            
            // Module Status (if not exists)
            if (!Schema::hasColumn('modules', 'is_active')) {
                $table->boolean('is_active')->default(true)
                    ->comment('Whether this module is active and available');
            }

            // Add indexes for common queries
            $table->index('azure_group_id', 'idx_azure_group_id');
            $table->index('azure_enterprise_app_id', 'idx_azure_app_id');
            $table->index('is_active', 'idx_is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_azure_group_id');
            $table->dropIndex('idx_azure_app_id');
            $table->dropIndex('idx_is_active');
            
            // Drop columns
            $table->dropColumn([
                'azure_group_id',
                'azure_enterprise_app_id',
                'requires_group_assignment',
                'requires_app_role_assignment',
                'external_api_endpoint',
                'api_auth_method',
                'api_credentials',
            ]);
            
            // Only drop is_active if we added it
            if (Schema::hasColumn('modules', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};