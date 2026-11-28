<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'sppg_id',
        'title',
        'description',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function sppgUnit(): BelongsTo
    {
        return $this->belongsTo(SppgUnit::class, 'sppg_id');
    }
}
