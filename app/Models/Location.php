<?php

// Location Model (app/Models/Location.php)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'country',
        'timezone',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'location', 'code');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'location', 'code');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
