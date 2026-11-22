<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAllergy extends Model
{
    use HasFactory;

    protected $table = 'student_allergies';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'allergen_id',
        'medical_notes',
        'verification_status',
        'verified_by',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function allergen(): BelongsTo
    {
        return $this->belongsTo(Allergen::class, 'allergen_id');
    }
}
