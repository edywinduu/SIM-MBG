<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SppgStaff extends Model
{
    use HasFactory;

    protected $table = 'sppg_staff';

    public $timestamps = false;

    protected $fillable = [
        'sppg_id',
        'user_id',
        'staff_code',
        'position',
        'assigned_school_id',
        'status',
    ];

    public function sppgUnit(): BelongsTo
    {
        return $this->belongsTo(SppgUnit::class, 'sppg_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedSchool(): BelongsTo
    {
        return $this->belongsTo(School::class, 'assigned_school_id');
    }
}
