<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolFeedback extends Model
{
    use HasFactory;

    protected $table = 'school_feedbacks';

    const UPDATED_AT = null;

    protected $fillable = [
        'school_id',
        'sppg_id',
        'date',
        'score',
        'waste_percentage',
        'container_temperature',
        'content',
        'kitchen_response',
        'status',
        'sender_name',
        'position',
        'created_at',
    ];

    protected $casts = [
        'date' => 'date',
        'score' => 'float',
        'waste_percentage' => 'float',
        'created_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function sppgUnit(): BelongsTo
    {
        return $this->belongsTo(SppgUnit::class, 'sppg_id');
    }
}
