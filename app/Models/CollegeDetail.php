<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CollegeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'college_id',
        'description',
        'additional_information',
    ];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function socialLinks(): MorphMany
    {
        return $this->morphMany(SocialLink::class, 'socialable');
    }
}
