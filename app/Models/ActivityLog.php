<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'sppg_id',
        'title',
        'description',
        'status',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the SPPG unit associated with the activity log.
     */
    public function sppgUnit(): BelongsTo
    {
        return $this->belongsTo(SppgUnit::class, 'sppg_id');
    }
}
