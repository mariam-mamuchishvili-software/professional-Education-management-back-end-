<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'profession_id',
        'name',
        'code',
        'capacity',
        'study_shift',
    ];

    public function profession()
    {
        return $this->belongsTo(Profession::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class);
    }
}