<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'conducted_by',
        'type',
        'status',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function conductedBy()
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class);
    }
}
