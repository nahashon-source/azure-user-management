<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'performed_by'
    ];

    // Remove these casts since columns don't exist
    // protected $casts = [
    //     'old_values' => 'array',
    //     'new_values' => 'array',
    // ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}