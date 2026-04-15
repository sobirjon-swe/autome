<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiSetting extends Model
{
    protected $fillable = [
        'branch_id',
        'shift_type',
        'day_type',
        'target_amount',
        'bonus_rules',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'bonus_rules' => 'array',
        'branch_id' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
