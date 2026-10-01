<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'profession_id',
        'name',
        'code',
        'capacity',
        'study_shift',
    ];

    /**
     * Limit the query to groups studying a profession that includes a module taught by
     * the given teacher. A missing teacher profile matches nothing.
     */
    #[Scope]
    protected function assignedTo(Builder $query, ?Teacher $teacher): void
    {
        $query->whereHas('profession.modules.teachers', fn (Builder $query) => $query->whereKey($teacher?->id));
    }

    public function profession()
    {
        return $this->belongsTo(Profession::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class);
    }
}
