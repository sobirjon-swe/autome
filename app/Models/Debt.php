<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'debtor_name',
        'phone',
        'amount',
        'paid_amount',
        'shift_id',
        'given_by',
        'status',
        'notes',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function givenBy()
    {
        return $this->belongsTo(User::class, 'given_by');
    }

    public function remainingAmount(): float
    {
        return $this->amount - $this->paid_amount;
    }
}
