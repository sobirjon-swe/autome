<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'shift_type',
        'scheduled_date',
        'status',
        'swapped_with',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function swappedWith()
    {
        return $this->belongsTo(User::class, 'swapped_with');
    }
}
