<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'wholesale_price',
        'selling_price',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'wholesale_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function branchStocks()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
