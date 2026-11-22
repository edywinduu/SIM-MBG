<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuSchedule extends Model
{
    use HasFactory;

    protected $table = 'menu_schedules';

    public $timestamps = false;

    protected $fillable = [
        'menu_id',
        'sppg_id',
        'date',
        'cycle',
        'cutoff_time',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeTargetDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function sppgUnit(): BelongsTo
    {
        return $this->belongsTo(SppgUnit::class, 'sppg_id');
    }

    public function selections(): HasMany
    {
        return $this->hasMany(MenuSelection::class, 'schedule_id');
    }
}
