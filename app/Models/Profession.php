<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'duration',
        'qualification',
    ];

    public function modules()
    {
        return $this->belongsToMany(Module::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}