<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialization',
    ];

    public function colleges()
    {
        return $this->belongsToMany(College::class, 'collage_teacher');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class);
    }
}