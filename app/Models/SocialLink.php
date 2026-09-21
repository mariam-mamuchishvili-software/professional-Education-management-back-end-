<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialLink extends Model
{
    use HasFactory;

    /**
     * Platforms supported by CollegeDetail and TeacherDetail social links, keyed by
     * the value stored in the `platform` column.
     *
     * @var array<string, string>
     */
    public const PLATFORMS = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'twitter' => 'X / Twitter',
        'tiktok' => 'TikTok',
        'website' => 'Website',
    ];

    protected $fillable = [
        'platform',
        'url',
    ];

    public function socialable(): MorphTo
    {
        return $this->morphTo();
    }
}
