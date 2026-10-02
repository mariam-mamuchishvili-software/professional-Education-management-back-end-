<?php

namespace App\Models;

use Database\Factories\TeacherEducationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherEducation extends Model
{
    /** @use HasFactory<TeacherEducationFactory> */
    use HasFactory;

    /**
     * "Education" is uncountable, so the table name is set explicitly instead of being guessed.
     */
    protected $table = 'teacher_educations';

    protected $fillable = [
        'institution',
        'degree',
        'specialization',
        'start_date',
        'end_date',
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
        ];
    }

    /**
     * @return BelongsTo<Teacher, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
