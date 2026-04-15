<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchStock extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'product_id',
        'stock_quantity',
        'min_stock_alert',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'min_stock_alert' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isLow(): bool
    {
        return $this->stock_quantity <= $this->min_stock_alert;
    }
}
