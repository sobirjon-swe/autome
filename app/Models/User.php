<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'phone',
        'password',
        'pin_code',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user')
            ->withPivot(['is_primary', 'assigned_at'])
            ->withTimestamps();
    }

    public function primaryBranch()
    {
        return $this->belongsToMany(Branch::class, 'branch_user')
            ->wherePivot('is_primary', true)
            ->withPivot(['is_primary', 'assigned_at']);
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

    public function kpiResults()
    {
        return $this->hasMany(KpiResult::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
