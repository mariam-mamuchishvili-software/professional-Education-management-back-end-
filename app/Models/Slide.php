<?php

namespace App\Models;

use App\Models\Concerns\ReplacesCloudinaryImageOnUpdate;
use Database\Factories\SlideFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Slide extends Model
{
    /** @use HasFactory<SlideFactory> */
    use HasFactory;

    use ReplacesCloudinaryImageOnUpdate;

    protected $fillable = [
        'college_id',
        'image',
        'title',
        'description',
    ];

    protected function cloudinaryImageAttributes(): array
    {
        return ['image'];
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }
}
