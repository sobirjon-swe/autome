<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiResult extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shift_id',
        'user_id',
        'target_amount',
        'actual_amount',
        'percentage',
        'bonus_amount',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
