<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $table = 'schools';

    public $timestamps = false;

    protected $fillable = [
        'npsn',
        'name',
        'education_level',
        'district',
        'city',
        'address',
        'pic_name',
        'sppg_id',
        'status',
    ];

    public function sppgUnit(): BelongsTo
    {
        return $this->belongsTo(SppgUnit::class, 'sppg_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'school_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class, 'school_id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(SchoolFeedback::class, 'school_id');
    }
}
