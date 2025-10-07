<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        // Azure Integration
        'azure_group_id',
        'azure_enterprise_app_id',
        'requires_group_assignment',
        'requires_app_role_assignment',
        // External API Integration
        'external_api_endpoint',
        'api_auth_method',
        'api_credentials',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_group_assignment' => 'boolean',
        'requires_app_role_assignment' => 'boolean',
    ];

    protected $hidden = [
        'api_credentials', // Hide encrypted credentials from JSON output
    ];

    /**
     * Relationships
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_modules')
                    ->withPivot('role_id', 'location', 'assigned_at', 'external_system_id', 'external_sync_status')
                    ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'module_roles')
                    ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequiresAzureProvisioning($query)
    {
        return $query->where(function ($q) {
            $q->where('requires_group_assignment', true)
              ->orWhere('requires_app_role_assignment', true);
        });
    }

    public function scopeHasExternalApi($query)
    {
        return $query->whereNotNull('external_api_endpoint');
    }

    /**
     * Azure Integration Helper Methods
     */
    public function requiresAzureProvisioning(): bool
    {
        return $this->requires_group_assignment || $this->requires_app_role_assignment;
    }

    public function requiresGroupAssignment(): bool
    {
        return $this->requires_group_assignment && !empty($this->azure_group_id);
    }

    public function requiresAppRoleAssignment(): bool
    {
        return $this->requires_app_role_assignment && !empty($this->azure_enterprise_app_id);
    }

    public function hasAzureGroupConfigured(): bool
    {
        return !empty($this->azure_group_id);
    }

    public function hasAzureAppConfigured(): bool
    {
        return !empty($this->azure_enterprise_app_id);
    }

    /**
     * External API Helper Methods
     */
    public function hasExternalApi(): bool
    {
        return !empty($this->external_api_endpoint);
    }

    public function requiresApiAuthentication(): bool
    {
        return $this->hasExternalApi() && $this->api_auth_method !== 'none';
    }

    public function getApiCredentials(): ?array
    {
        if (empty($this->api_credentials)) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($this->api_credentials);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            Log::error("Failed to decrypt API credentials for module {$this->code}", [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function setApiCredentials(array $credentials): void
    {
        $json = json_encode($credentials);
        $this->api_credentials = Crypt::encryptString($json);
    }

    /**
     * Provisioning Status Methods
     */
    public function isFullyConfigured(): bool
    {
        // Check if module has all required configurations
        $azureConfigured = !$this->requiresAzureProvisioning() || 
                          ($this->hasAzureGroupConfigured() && $this->hasAzureAppConfigured());
        
        $apiConfigured = !$this->hasExternalApi() || 
                        ($this->requiresApiAuthentication() && !empty($this->api_credentials));

        return $azureConfigured && $apiConfigured;
    }

    public function getIntegrationType(): string
    {
        $types = [];

        if ($this->requiresAzureProvisioning()) {
            $types[] = 'Azure AD';
        }

        if ($this->hasExternalApi()) {
            $types[] = 'External API';
        }

        return empty($types) ? 'None' : implode(' + ', $types);
    }

    public function getProvisioningRequirements(): array
    {
        return [
            'azure_group' => $this->requiresGroupAssignment(),
            'azure_app_role' => $this->requiresAppRoleAssignment(),
            'external_api' => $this->hasExternalApi(),
            'fully_configured' => $this->isFullyConfigured(),
        ];
    }

    /**
     * Attribute Accessors
     */
    public function getIntegrationStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if (!$this->isFullyConfigured()) {
            return 'incomplete';
        }

        return 'ready';
    }

    public function getIntegrationStatusBadgeClassAttribute(): string
    {
        return match($this->integration_status) {
            'ready' => 'badge-success',
            'incomplete' => 'badge-warning',
            'inactive' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    /**
 * Get the Azure group ID for a specific role
 */
public function getAzureGroupForRole(int $roleId): ?array
{
    $mapping = ModuleRoleGroup::where('module_id', $this->id)
        ->where('role_id', $roleId)
        ->first();
    
    if (!$mapping) {
        return null;
    }
    
    return [
        'group_id' => $mapping->azure_group_id,
        'group_name' => $mapping->azure_group_name,
    ];
}

/**
 * Check if this module has a mapping for a specific role
 */
public function hasRoleMapping(int $roleId): bool
{
    return ModuleRoleGroup::where('module_id', $this->id)
        ->where('role_id', $roleId)
        ->exists();
}

/**
 * Get all role mappings for this module
 */
public function roleGroups()
{
    return $this->hasMany(ModuleRoleGroup::class);
}
}
