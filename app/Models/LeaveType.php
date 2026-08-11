<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'days_per_year',
        'is_paid',
    ];

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }
}