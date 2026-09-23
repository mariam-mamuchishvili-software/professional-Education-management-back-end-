<?php

namespace App\Models;

use App\Models\Concerns\ReplacesCloudinaryImageOnUpdate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'logo',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected function cloudinaryImageAttributes(): array
    {
        return ['poster', 'logo'];
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'collage_teacher');
    }

    public function detail()
    {
        return $this->hasOne(CollegeDetail::class);
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class);
    }

    public function professions(): BelongsToMany
    {
        return $this->belongsToMany(Profession::class)->withTimestamps();
    }

    /**
     * Groups studying a profession taught at this college, derived from the modules its
     * teachers teach. Not a Relation instance, so it can't be eager loaded via with() —
     * callers must ->get() it and attach the result via setRelation('groups', ...) explicitly.
     */
    public function relatedGroups(): Builder
    {
        return Group::query()->with('profession')->whereHas(
            'profession.modules.teachers.colleges',
            fn (Builder $query) => $query->whereKey($this->id)
        );
    }
}
