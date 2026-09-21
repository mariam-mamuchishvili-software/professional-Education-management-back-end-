<?php

namespace App\Models;

use App\Models\Concerns\ReplacesCloudinaryImageOnUpdate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    use ReplacesCloudinaryImageOnUpdate;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialization',
        'image',
    ];

    protected function cloudinaryImageAttribute(): string
    {
        return 'image';
    }

    public function colleges()
    {
        return $this->belongsToMany(College::class, 'collage_teacher');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class);
    }

    public function detail()
    {
        return $this->hasOne(TeacherDetail::class);
    }
}