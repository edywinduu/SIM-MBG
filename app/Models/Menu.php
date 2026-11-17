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
        'carbs',
        'protein',
        'fat',
        'fiber',
        'image',
        'is_active',
    ];

    // Accessor: Mengubah string lauk di 'description' menjadi array siap pakai
    public function getItemsAttribute(): array
    {
        if (empty($this->description)) {
            return [];
        }

        return array_map('trim', explode(',', $this->description));
    }

    /**
     * Accessor: Menghitung persentase makronutrisi (Protein, Karbohidrat, Lemak)
     * berdasarkan proporsi gram nutrisi di tabel menus.
     *
     * @return array{protein: int, carbs: int, fat: int}
     */
    public function getMacroPercentagesAttribute(): array
    {
        $protein = (float) ($this->protein ?? 0);
        $carbs = (float) ($this->carbs ?? 0);
        $fat = (float) ($this->fat ?? 0);
        $total = $protein + $carbs + $fat;

        if ($total <= 0) {
            return [
                'protein' => 0,
                'carbs' => 0,
                'fat' => 0,
            ];
        }

        $proteinPct = (int) round(($protein / $total) * 100);
        $carbsPct = (int) round(($carbs / $total) * 100);
        $fatPct = max(0, 100 - $proteinPct - $carbsPct);

        return [
            'protein' => $proteinPct,
            'carbs' => $carbsPct,
            'fat' => $fatPct,
        ];
    }

    public function optionsMenu()
    {
        return $this->hasMany(OptionsMenu::class);
    }
}
