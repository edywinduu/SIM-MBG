<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'full_name',
        'role',
        'institution',
        'status',
        'remember_token',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the name of the password attribute for authentication.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * Get the password for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Check if user has admin role.
     */
    public function isAdmin(): bool
    {
        return strtolower($this->role ?? '') === 'admin';
    }

    /**
     * Check if user has sppg role.
     */
    public function isSppg(): bool
    {
        $role = strtolower($this->role ?? '');

        return in_array($role, ['sppg', 'guru', 'petugas', 'petugas_sppg'], true);
    }

    /**
     * Check if user has student/siswa role.
     */
    public function isStudent(): bool
    {
        $role = strtolower($this->role ?? '');

        return in_array($role, ['student', 'siswa'], true);
    }

    /**
     * Get the student profile associated with the user.
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }
}
