<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'from_branch_id',
        'to_branch_id',
        'transferred_by',
        'status',
        'confirmed_by',
        'notes',
        'confirmed_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
