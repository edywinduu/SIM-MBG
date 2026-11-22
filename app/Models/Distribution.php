<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Distribution extends Model
{
    use HasFactory;

    protected $table = 'distributions';

    public $timestamps = false;

    protected $fillable = [
        'sppg_id',
        'school_id',
        'date',
        'portion_count',
        'eta_start',
        'eta_end',
        'status',
        'fleet',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function sppgUnit(): BelongsTo
    {
        return $this->belongsTo(SppgUnit::class, 'sppg_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}
