<?php

namespace App\Models;

use App\Models\Concerns\ReplacesCloudinaryImageOnUpdate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    use ReplacesCloudinaryImageOnUpdate;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'birth_date',
        'image',
    ];

    protected function cloudinaryImageAttribute(): string
    {
        return 'image';
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class);
    }
}