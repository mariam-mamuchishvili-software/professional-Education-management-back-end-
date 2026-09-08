<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialization',
    ];

    public function colleges()
    {
        return $this->belongsToMany(College::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class);
    }
}