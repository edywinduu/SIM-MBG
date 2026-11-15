<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
     * Get the menus selected by the student.
     */
    public function selectedMenus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_selections', 'student_id', 'menu_id')
            ->withPivot('schedule_id', 'consumption_status', 'created_at');
    }
}
