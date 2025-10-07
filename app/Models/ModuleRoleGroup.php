<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleRoleGroup extends Model
{
    protected $fillable = [
        'module_id',
        'role_id',
        'azure_group_id',
        'azure_group_name',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}