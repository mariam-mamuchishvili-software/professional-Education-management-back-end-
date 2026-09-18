<?php

namespace App\Models;

use App\Models\Concerns\ReplacesCloudinaryImageOnUpdate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    use HasFactory;
    use ReplacesCloudinaryImageOnUpdate;

    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'website',
        'poster',
    ];

    protected function cloudinaryImageAttribute(): string
    {
        return 'poster';
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'collage_teacher');
    }

    /**
     * Professions taught at this college, derived from the modules its teachers teach.
     * Deliberately not named professions() / not a Relation instance: College has no direct
     * FK/pivot to Profession, so this can't be eager loaded via with() — callers must
     * ->get() it and attach the result via setRelation('professions', ...) explicitly.
     */
    public function relatedProfessions(): Builder
    {
        return Profession::query()->whereHas(
            'modules.teachers.colleges',
            fn (Builder $query) => $query->whereKey($this->id)
        );
    }

    /**
     * Groups studying a profession taught at this college, derived the same way as
     * relatedProfessions(). Not eager loadable via with() — see relatedProfessions().
     */
    public function relatedGroups(): Builder
    {
        return Group::query()->with('profession')->whereHas(
            'profession.modules.teachers.colleges',
            fn (Builder $query) => $query->whereKey($this->id)
        );
    }
}