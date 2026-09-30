<?php

namespace App\Models;

use Database\Factories\PresenterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presenter extends Model
{
    /** @use HasFactory<PresenterFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];
}
