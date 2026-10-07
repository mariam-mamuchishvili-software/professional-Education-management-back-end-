<?php

namespace App\Models;

use App\Models\Concerns\ReplacesCloudinaryImageOnUpdate;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * A teacher signs in to their own cabinet (the "teacher" panel and guard) with the
 * email and password stored on this record.
 */
class Teacher extends Authenticatable implements FilamentUser, HasName
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use ReplacesCloudinaryImageOnUpdate;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'phone',
        'specialization',
        'image',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Teachers are created by an administrator who already knows their email address, so the
     * address counts as verified from the start unless a verification time is given explicitly.
     */
    protected static function booted(): void
    {
        static::creating(function (Teacher $teacher): void {
            if (! $teacher->isDirty('email_verified_at')) {
                $teacher->email_verified_at = now();
            }
        });
    }

    /**
     * The first and last name joined, used for compact labels in the admin panel.
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    protected function cloudinaryImageAttributes(): array
    {
        return ['image'];
    }

    /**
     * Teachers may only enter their own cabinet, never the admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'teacher';
    }

    public function getFilamentName(): string
    {
        return $this->full_name;
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

    /**
     * Work experience the teacher has gained at any organization.
     *
     * @return HasMany<TeacherWorkExperience, $this>
     */
    public function workExperiences(): HasMany
    {
        return $this->hasMany(TeacherWorkExperience::class);
    }

    /**
     * Degrees and qualifications the teacher has obtained.
     *
     * @return HasMany<TeacherEducation, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(TeacherEducation::class);
    }

    /**
     * Trainings and certificates the teacher has completed, organized by anyone.
     *
     * @return HasMany<TeacherTraining, $this>
     */
    public function trainings(): HasMany
    {
        return $this->hasMany(TeacherTraining::class);
    }
}
