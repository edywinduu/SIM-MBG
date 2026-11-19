<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pickup extends Model
{
    use HasFactory;

    protected $table = 'pickups';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'date',
        'status',
        'pickup_time',
    ];

    protected $casts = [
        'date' => 'date',
        'pickup_time' => 'datetime',
    ];

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function isClaimed(): bool
    {
        return $this->status === 'picked_up';
    }

    public function markAsClaimed(): bool
    {
        $this->status = 'picked_up';
        $this->pickup_time = now();

        return $this->save();
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
