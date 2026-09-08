<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'duration',
        'credits',
    ];

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class);
    }

    public function professions()
    {
        return $this->belongsToMany(Profession::class);
    }
}