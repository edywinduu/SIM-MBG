<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Allergen extends Model
{
    use HasFactory;

    protected $table = 'allergens';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_allergies', 'allergen_id', 'student_id')
            ->withPivot('medical_notes', 'verification_status', 'verified_by');
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_allergens', 'allergen_id', 'menu_id')
            ->withPivot('type');
    }
}
