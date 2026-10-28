<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

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
        return strtolower($this->role ?? '') === 'sppg';
    }

    /**
     * Check if user has student/siswa role.
     */
    public function isStudent(): bool
    {
        $role = strtolower($this->role ?? '');
        return in_array($role, ['student', 'siswa'], true);
    }
}
