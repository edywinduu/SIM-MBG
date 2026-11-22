<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'holidays';

    protected $fillable = [
        'date',
        'name',
        'type',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function scopeTargetDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public static function checkHolidayStatus(Carbon $date): ?string{
        if ($date->isWeekend()){
            return 'Selamat Menikmati Hari Libur!';
        }

        return null;
    }
}
