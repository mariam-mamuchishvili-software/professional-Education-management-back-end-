<?php

namespace App\Filament\Widgets;

use App\Models\College;
use App\Models\Group;
use App\Models\Module;
use App\Models\Profession;
use App\Models\Student;
use App\Models\Teacher;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EducationOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Colleges', College::query()->count())
                ->icon('heroicon-o-building-library'),

            Stat::make('Teachers', Teacher::query()->count())
                ->icon('heroicon-o-identification'),

            Stat::make('Professions', Profession::query()->count())
                ->icon('heroicon-o-briefcase'),

            Stat::make('Modules', Module::query()->count())
                ->icon('heroicon-o-book-open'),

            Stat::make('Groups', Group::query()->count())
                ->icon('heroicon-o-user-group'),

            Stat::make('Students', Student::query()->count())
                ->icon('heroicon-o-users'),
        ];
    }
}
