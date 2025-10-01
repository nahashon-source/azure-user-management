<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModule extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'user_modules';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'module_id',
        'role_id',
        'location',
        'assigned_at',
        'external_system_id',
        'external_sync_status',
        'external_synced_at',
        'external_sync_error',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'assigned_at' => 'datetime',
        'external_synced_at' => 'datetime',
    ];

    /**
     * Get the user that owns the module assignment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the module for this assignment.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the role for this assignment.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Scope to get only synced assignments
     */
    public function scopeSynced($query)
    {
        return $query->where('external_sync_status', 'synced');
    }

    /**
     * Scope to get only failed assignments
     */
    public function scopeFailed($query)
    {
        return $query->where('external_sync_status', 'failed');
    }

    /**
     * Scope to get only pending assignments
     */
    public function scopePending($query)
    {
        return $query->where('external_sync_status', 'pending');
    }

    /**
     * Check if this assignment is synced to external system
     */
    public function isSynced(): bool
    {
        return $this->external_sync_status === 'synced';
    }

    /**
     * Check if this assignment failed external sync
     */
    public function hasFailed(): bool
    {
        return $this->external_sync_status === 'failed';
    }

    /**
     * Check if this assignment is pending external sync
     */
    public function isPending(): bool
    {
        return $this->external_sync_status === 'pending';
    }

    /**
     * Mark assignment as synced
     */
    public function markAsSynced(?string $externalSystemId = null): void
    {
        $this->update([
            'external_sync_status' => 'synced',
            'external_synced_at' => now(),
            'external_system_id' => $externalSystemId,
            'external_sync_error' => null,
        ]);
    }

    /**
     * Mark assignment as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'external_sync_status' => 'failed',
            'external_sync_error' => $errorMessage,
        ]);
    }

    /**
     * Mark assignment as pending
     */
    public function markAsPending(): void
    {
        $this->update([
            'external_sync_status' => 'pending',
            'external_synced_at' => null,
            'external_sync_error' => null,
        ]);
    }

    /**
     * Get formatted sync status for display
     */
    public function getSyncStatusAttribute(): string
    {
        return match ($this->external_sync_status) {
            'synced' => 'Synced',
            'failed' => 'Failed',
            'pending' => 'Pending',
            default => 'Unknown',
        };
    }

    /**
     * Get status badge class for UI
     */
    public function getSyncStatusBadgeClassAttribute(): string
    {
        return match ($this->external_sync_status) {
            'synced' => 'badge-success',
            'failed' => 'badge-danger',
            'pending' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}