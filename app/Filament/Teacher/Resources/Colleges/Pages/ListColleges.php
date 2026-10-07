<?php

namespace App\Filament\Teacher\Resources\Colleges\Pages;

use App\Filament\Teacher\Resources\Colleges\CollegeResource;
use Filament\Resources\Pages\ListRecords;

class ListColleges extends ListRecords
{
    protected static string $resource = CollegeResource::class;
}
