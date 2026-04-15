<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reconciliation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'shift_id',
        'total_online_sales',
        'total_bank_received',
        'difference',
        'status',
        'checked_by',
        'notes',
        'checked_at',
    ];

    protected $casts = [
        'total_online_sales' => 'decimal:2',
        'total_bank_received' => 'decimal:2',
        'difference' => 'decimal:2',
        'checked_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
