<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'shift_type',
        'opened_at',
        'closed_at',
        'opening_cash',
        'closing_cash_declared',
        'closing_cash_confirmed',
        'confirmed_by',
        'status',
        'target_amount',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_cash' => 'decimal:2',
        'closing_cash_declared' => 'decimal:2',
        'closing_cash_confirmed' => 'decimal:2',
        'target_amount' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function kpiResult()
    {
        return $this->hasOne(KpiResult::class);
    }

    public function closingHandover()
    {
        return $this->hasOne(ShiftHandover::class, 'closing_shift_id');
    }

    public function openingHandover()
    {
        return $this->hasOne(ShiftHandover::class, 'opening_shift_id');
    }

    public function reconciliation()
    {
        return $this->hasOne(Reconciliation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
