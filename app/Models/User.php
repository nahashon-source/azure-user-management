<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'phone',
        'location',
        'company_id',
        'status',
        'azure_id',
        'azure_upn',
        'azure_display_name',
        'last_login_at',
        'disabled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    /**
     * Get the company that the user belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The modules that belong to the user.
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'user_modules')
                    ->withPivot('role_id', 'assigned_at')
                    ->withTimestamps();
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
                    ->withPivot('assigned_at')
                    ->withTimestamps();
    }

    /**
     * Get the audit logs for the user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the procedures related to the user.
     */
    public function procedures(): HasMany
    {
        return $this->hasMany(Procedure::class);
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include users by location.
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Scope a query to search users by name, email, or employee_id.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('email', 'LIKE', "%{$term}%")
              ->orWhere('employee_id', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Get the user's initials for avatar.
     */
    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);
        return count($names) >= 2 
            ? strtoupper($names[0][0] . $names[1][0])
            : strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Get the user's status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'active' => 'badge-success',
            'pending' => 'badge-warning',
            'inactive' => 'badge-danger',
            default => 'badge-secondary'
        };
    }

    /**
     * Check if user has a specific module.
     */
    public function hasModule($moduleCode)
    {
        return $this->modules()->where('code', $moduleCode)->exists();
    }

    /**
     * Check if user has a specific role in a module.
     */
    public function hasRoleInModule($moduleCode, $roleName)
    {
        return $this->modules()
                   ->where('code', $moduleCode)
                   ->whereHas('roles', function ($q) use ($roleName) {
                       $q->where('name', $roleName);
                   })
                   ->exists();
    }

    /**
     * Get user's permissions based on assigned modules and roles.
     */
    public function getPermissions()
    {
        $permissions = [];
        
        foreach ($this->modules as $module) {
            if (isset($module->pivot->role_id)) {
                $role = Role::find($module->pivot->role_id);
                if ($role) {
                    $permissions[$module->code] = [
                        'module' => $module->name,
                        'role' => $role->name,
                        'permissions' => $role->permissions ?? []
                    ];
                }
            }
        }
        
        return $permissions;
    }
}