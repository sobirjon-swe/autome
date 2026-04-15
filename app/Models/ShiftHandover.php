<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftHandover extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'closing_shift_id',
        'opening_shift_id',
        'declared_amount',
        'confirmed_amount',
        'difference',
        'status',
        'notes',
        'confirmed_at',
    ];

    protected $casts = [
        'declared_amount' => 'decimal:2',
        'confirmed_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function closingShift()
    {
        return $this->belongsTo(Shift::class, 'closing_shift_id');
    }

    public function openingShift()
    {
        return $this->belongsTo(Shift::class, 'opening_shift_id');
    }
}
