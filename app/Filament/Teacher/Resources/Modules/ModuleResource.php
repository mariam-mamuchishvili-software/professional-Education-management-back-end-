<?php

namespace App\Filament\Teacher\Resources\Modules;

use App\Filament\Teacher\RelationManagers\StudentsRelationManager;
use App\Filament\Teacher\Resources\Modules\Pages\ListModules;
use App\Filament\Teacher\Resources\Modules\Pages\ViewModule;
use App\Filament\Teacher\Resources\Modules\Schemas\ModuleInfolist;
use App\Filament\Teacher\Resources\Modules\Tables\ModulesTable;
use App\Models\Module;
use App\Models\Teacher;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modules the signed-in teacher teaches. Read-only (with the students), and limited to the signed-in
 * teacher by the query itself, so the admin-only ModulePolicy is not consulted.
 */
class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationLabel = 'My modules';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldSkipAuthorization = true;

    public static function infolist(Schema $schema): Schema
    {
        return ModuleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModulesTable::configure($table);
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

    public static function getRelations(): array
    {
        return [
            StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModules::route('/'),
            'view' => ViewModule::route('/{record}'),
        ];
    }
}
