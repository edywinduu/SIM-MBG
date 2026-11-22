<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SppgUnit extends Model
{
    use HasFactory;

    protected $table = 'sppg_units';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'region',
        'address',
        'capacity',
        'contact_person',
        'phone',
        'status',
    ];

    public function schools(): HasMany
    {
        return $this->hasMany(School::class, 'sppg_id');
    }

    public function staff(): HasMany
    {
        return $this->hasMany(SppgStaff::class, 'sppg_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class, 'sppg_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(MenuSchedule::class, 'sppg_id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(SchoolFeedback::class, 'sppg_id');
    }

    public function kitchenAudits(): HasMany
    {
        return $this->hasMany(KitchenAudit::class, 'sppg_id');
    }
}
