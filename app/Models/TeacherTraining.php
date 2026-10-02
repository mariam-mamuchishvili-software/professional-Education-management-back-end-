<?php

namespace App\Models;

use App\Models\Concerns\ReplacesCloudinaryImageOnUpdate;
use Database\Factories\TeacherTrainingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherTraining extends Model
{
    /** @use HasFactory<TeacherTrainingFactory> */
    use HasFactory;

    use ReplacesCloudinaryImageOnUpdate;

    protected $fillable = [
        'title',
        'organizer',
        'certificate_number',
        'issue_date',
        'expiry_date',
        'certificate_url',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    protected function cloudinaryImageAttributes(): array
    {
        return ['certificate_url'];
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
