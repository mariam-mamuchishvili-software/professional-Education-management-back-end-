<?php

namespace App\Models;

use Database\Factories\TeacherWorkExperienceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherWorkExperience extends Model
{
    /** @use HasFactory<TeacherWorkExperienceFactory> */
    use HasFactory;

    protected $attributes = [
        'is_current' => false,
    ];

    protected $fillable = [
        'teacher_id',
        'organization',
        'position',
        'start_date',
        'end_date',
        'is_current',
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
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /**
     * A current position has no end date, whichever way (API or admin panel) it was saved.
     */
    protected static function booted(): void
    {
        static::saving(function (TeacherWorkExperience $workExperience) {
            if ($workExperience->is_current) {
                $workExperience->end_date = null;
            }
        });
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
