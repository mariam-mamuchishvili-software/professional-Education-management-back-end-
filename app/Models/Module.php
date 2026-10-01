<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'duration',
        'credits',
    ];

    /**
     * Limit the query to records the given teacher is assigned to. A missing teacher
     * profile matches nothing.
     */
    #[Scope]
    protected function assignedTo(Builder $query, ?Teacher $teacher): void
    {
        $query->whereHas('teachers', fn (Builder $query) => $query->whereKey($teacher?->id));
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class);
    }

    public function professions()
    {
        return $this->belongsToMany(Profession::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class);
    }
}
