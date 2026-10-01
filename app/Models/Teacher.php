<?php

namespace App\Models;

use App\Models\Concerns\ReplacesCloudinaryImageOnUpdate;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teacher extends Model
{
    use HasFactory;
    use ReplacesCloudinaryImageOnUpdate;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialization',
        'image',
        'user_id',
    ];

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
     * The user account this teacher signs in with, if one has been linked.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
