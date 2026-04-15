<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user')
            ->withPivot(['is_primary', 'assigned_at']);
    }

    public function stocks()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
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

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function kpiSettings()
    {
        return $this->hasMany(KpiSetting::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function reconciliations()
    {
        return $this->hasMany(Reconciliation::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'from_branch_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'to_branch_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
