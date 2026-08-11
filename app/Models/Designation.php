<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    protected $fillable = [
        'department_id',
        'name',
        'code',
        'description',
        'status'
    ];

    protected $casts = [
        'status'=>'boolean'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}