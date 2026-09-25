<?php

namespace App\Models;

use App\Models\Concerns\ReplacesCloudinaryImageOnUpdate;
use Database\Factories\TrainingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    /** @use HasFactory<TrainingFactory> */
    use HasFactory;

    use ReplacesCloudinaryImageOnUpdate;

    protected $fillable = [
        'title',
        'description',
        'poster',
        'video_link',
    ];

    protected function cloudinaryImageAttributes(): array
    {
        return ['poster'];
    }
}
