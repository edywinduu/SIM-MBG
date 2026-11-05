<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    // Kolom-kolom yang diizinkan untuk mass-assignment
    protected $fillable = [
        'name',
        'description',
        'category',
        'calories',
    ];

    // Accessor: Mengubah string lauk di 'description' menjadi array siap pakai
    public function getItemsAttribute(): array
    {
        if (empty($this->description)) {
            return [];
        }

        return array_map('trim', explode(',', $this->description));
    }
}
