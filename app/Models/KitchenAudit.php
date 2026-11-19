<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenAudit extends Model
{
    use HasFactory;

    protected $table = 'kitchen_audits';

    public $timestamps = false;

    protected $fillable = [
        'sppg_id',
        'audit_date',
        'inspector_name',
        'hygiene_score',
        'notes',
        'status',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'hygiene_score' => 'float',
    ];

    public function sppgUnit(): BelongsTo
    {
        return $this->belongsTo(SppgUnit::class, 'sppg_id');
    }
}
