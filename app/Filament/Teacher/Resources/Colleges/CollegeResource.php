<?php

namespace App\Filament\Teacher\Resources\Colleges;

use App\Filament\Teacher\Resources\Colleges\Pages\ListColleges;
use App\Filament\Teacher\Resources\Colleges\Tables\CollegesTable;
use App\Models\College;
use App\Models\Teacher;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Colleges the signed-in teacher works at. Read-only, and limited to the signed-in
 * teacher by the query itself, so the admin-only CollegePolicy is not consulted.
 */
class CollegeResource extends Resource
{
    protected static ?string $model = College::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'My colleges';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldSkipAuthorization = true;

    public static function table(Table $table): Table
    {
        return CollegesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var Teacher $teacher */
        $teacher = Filament::auth()->user();

        return parent::getEloquentQuery()->assignedTo($teacher);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListColleges::route('/'),
        ];
    }
}
