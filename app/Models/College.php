<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'website',
    ];

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class);
    }
}