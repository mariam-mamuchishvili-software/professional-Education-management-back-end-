<?php

namespace App\Filament\Teacher\Resources\Groups;

use App\Filament\Teacher\RelationManagers\StudentsRelationManager;
use App\Filament\Teacher\Resources\Groups\Pages\ListGroups;
use App\Filament\Teacher\Resources\Groups\Pages\ViewGroup;
use App\Filament\Teacher\Resources\Groups\Schemas\GroupInfolist;
use App\Filament\Teacher\Resources\Groups\Tables\GroupsTable;
use App\Models\Group;
use App\Models\Teacher;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Groups studying a profession that includes a module the signed-in teacher teaches. Read-only (with the students), and limited to the signed-in
 * teacher by the query itself, so the admin-only GroupPolicy is not consulted.
 */
class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'My groups';

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $shouldSkipAuthorization = true;

    public static function infolist(Schema $schema): Schema
    {
        return GroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        /** @var Teacher $teacher */
        $teacher = Filament::auth()->user();

        return parent::getEloquentQuery()->with('profession')->assignedTo($teacher);
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
            'index' => ListGroups::route('/'),
            'view' => ViewGroup::route('/{record}'),
        ];
    }
}
