<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuSelection extends Model
{
    use HasFactory;

    protected $table = 'menu_selections';

    const UPDATED_AT = null;

    protected $fillable = [
        'student_id',
        'schedule_id',
        'menu_id',
        'consumption_status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MenuSchedule::class, 'schedule_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
