<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'nisn',
        'name',
        'school_id',
        'class_name',
        'recipient_status',
        'profile_picture',
    ];

    /**
     * Get the user account associated with the student.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the school associated with the student.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Get the menus selected by the student.
     */
    public function selectedMenus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_selections', 'student_id', 'menu_id')
            ->withPivot('schedule_id', 'consumption_status', 'created_at');
    }

    /**
     * Get the menu selections records.
     */
    public function selections(): HasMany
    {
        return $this->hasMany(MenuSelection::class, 'student_id');
    }

    /**
     * Get the pickups associated with the student.
     */
    public function pickups(): HasMany
    {
        return $this->hasMany(Pickup::class, 'student_id');
    }

    /**
     * Get the reviews written by the student.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(StudentReview::class, 'student_id');
    }

    /**
     * Get the allergens the student is allergic to.
     */
    public function allergies(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'student_allergies', 'student_id', 'allergen_id')
            ->withPivot('medical_notes', 'verification_status', 'verified_by');
    }

    /**
     * Get the raw student allergy pivot records.
     */
    public function studentAllergies(): HasMany
    {
        return $this->hasMany(StudentAllergy::class, 'student_id');
    }

    /**
     * Check if the student has registered allergies.
     */
    public function hasAllergies(): bool
    {
        return $this->allergies->isNotEmpty();
    }

    /**
     * Get menu type for display.
     */
    public function getMenuTypeAttribute(): string
    {
        return $this->hasAllergies() ? 'Menu Khusus' : 'Reguler';
    }

    /**
     * Get summary of allergies for display.
     */
    public function getAllergiesSummaryAttribute(): string
    {
        if ($this->allergies->isEmpty()) {
            return '-';
        }

        return $this->allergies->pluck('name')->join(', ');
    }
}
